<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Probance\M2connector\Model\BatchIterator as Iterator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Repository as EavRepository;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Model\Store;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\Flow\Renderer\Factory as RendererFactory;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Probance\M2connector\Model\Flow\Formater\CatalogArticleFormater;
use Probance\M2connector\Model\ResourceModel\MappingArticle\CollectionFactory as ArticleMappingCollectionFactory;

class CatalogArticle extends AbstractFlow
{
    /**
     * Suffix use for filename defined configuration path
     */
    const EXPORT_CONF_FILENAME_SUFFIX = '_article';

    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'catalog';

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var CatalogArticleFormater
     */
    protected $catalogArticleFormater;

    /**
     * @var RendererFactory
     */
    protected $rendererFactory;

    /**
     * @var TypeFactory
     */
    protected $typeFactory;

    /**
     * @var EavRepository
     */
    protected $eavRepository;

    /**
     * @var array
     */
    protected $processedProducts = [];

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var int
     */
    protected $exportStore;

    /**
     * CatalogArticle constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator

     * @param ArticleMappingCollectionFactory $articleMappingCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param CatalogArticleFormater $catalogArticleFormater
     * @param RendererFactory $rendererFactory
     * @param TypeFactory $typeFactory
     * @param EavRepository $eavRepository
     * @param ProductFactory $productFactory
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        Iterator $iterator,

        ArticleMappingCollectionFactory $articleMappingCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        CatalogArticleFormater $catalogArticleFormater,
        RendererFactory $rendererFactory,
        TypeFactory $typeFactory,
        EavRepository $eavRepository,
        ProductFactory $productFactory
    )
    {
        parent::__construct(
            $probanceHelper,
            $directoryList,
            $file,
            $ftp,
            $iterator
        );

        $this->flowMappingCollectionFactory = $articleMappingCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->catalogArticleFormater = $catalogArticleFormater;
        $this->rendererFactory = $rendererFactory;
        $this->typeFactory = $typeFactory;
        $this->eavRepository = $eavRepository;
        $this->productFactory = $productFactory;
    }

    /**
     * @param $entity
     */
    public function iterateCallback($entity)
    {
        try {
            $data = [];
            if ($this->exportStore != Store::DEFAULT_STORE_ID) {
                $product = $this->productFactory->create()->setStoreId($this->exportStore)->load($entity->getId());
                $this->catalogArticleFormater->setExportStore($this->exportStore);
            } else {
                $product = $this->productRepository->getById($entity->getId());
                $this->catalogArticleFormater->setExportStore(Store::DEFAULT_STORE_ID);
            }
            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                $childs = $this->configurable->getUsedProducts($product);
            } else {
                $childs = [$product];
            }
        } catch (NoSuchEntityException $e) {
            return;
        }

        foreach ($childs as $child) {
            try {
                if (!in_array($child->getId(), $this->processedProducts)) {
                    if ($this->progressBar) {
                        $this->progressBar->setMessage(__('Processing: %1', $child->getSku()), 'status');
                    }
                    foreach ($this->mapping['items'] as $mappingItem) {
                        $key = $mappingItem['magento_attribute'];
                        $dataKey = $key . '-' . $mappingItem['position'];
                        $method = 'get' . $this->catalogArticleFormater->convertToCamelCase($key);

                        $data[$dataKey] = '';

                        if (!empty($mappingItem['user_value'])) {
                            $data[$dataKey] = $mappingItem['user_value'];
                            continue;
                        }

                        if (method_exists($this->catalogArticleFormater, $method)) {
                            $data[$dataKey] = $this->catalogArticleFormater->$method($child);
                        } else if (method_exists($child, $method)) {
                            $data[$dataKey] = $child->$method();
                        } else {
                            $customAttribute = $child->getCustomAttribute($key);
                            if ($customAttribute) {
                                $data[$dataKey] = $this->formatValueWithRenderer($key, $child);
                            }
                        }

                        $escaper = [
                            '~'.$this->probanceHelper->getFlowFormatValue('enclosure').'~'
                            => $this->probanceHelper->getFlowFormatValue('escape').$this->probanceHelper->getFlowFormatValue('enclosure')
                        ];

                        $data[$dataKey] = $this->typeFactory
                            ->getInstance($mappingItem['field_type'])
                            ->render($data[$dataKey], $mappingItem['field_limit']);
                    }

                    $this->processedProducts[] = $child->getId();

                    $this->file->filePutCsv(
                        $this->csv,
                        $this->probanceHelper->postProcessData($data),
                        $this->probanceHelper->getFlowFormatValue('field_separator'),
                        $this->probanceHelper->getFlowFormatValue('enclosure')
                    );

                }
            }  catch (\Exception $e) {
                $this->errors[] = [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ];
            }
        }
        unset($product);
        unset($childs);
        unset($data);
                    
        if ($this->progressBar) {
            $this->progressBar->advance();
        }
    }

    /**
     * Format value
     *
     * @param $code
     * @param $product
     * @return string
     */
    protected function formatValueWithRenderer($code, ProductInterface $product)
    {
        $value = '';

        try {
            $eavAttribute = $this->eavRepository->get($code);
            $value = $this->rendererFactory
                ->getInstance($eavAttribute->getFrontendInput())
                ->render($product, $eavAttribute);
        } catch (NoSuchEntityException $e) {

        }

        return $value;
    }

    /**
     * @param $storeId
     * @return array
     */
    public function getArrayCollection($storeId)
    {
        $productCollection = $this->productCollectionFactory->create();

        if (isset($this->range['from']) && isset($this->range['to'])) {
            $productCollection
                ->addAttributeToFilter('updated_at', ['from' => $this->range['from']])
                ->addAttributeToFilter('updated_at', ['to' => $this->range['to']]);
        }

        $this->exportStore = $this->probanceHelper->getFlowFormatValue('default_export_store');
        if (!$this->exportStore) $this->exportStore = $storeId;
        $productCollection->addStoreFilter($this->exportStore);

        $productCollection->addAttributeToFilter('status', Status::STATUS_ENABLED);

        if ($this->entityId) {
            $productCollection->addFieldToFilter($productCollection->getIdFieldName(), $this->entityId);
        }

        $currentPage = $this->checkForNextPage($productCollection);

        if ($this->progressBar) {
            $this->progressBar->setMessage(__('Treating page %1', $currentPage), 'warn');
        }

        $count = min($this->getLimit(), $productCollection->getSize());

        return [
            [
                'object'    => $productCollection,
                'count'     => $count,
                'callback'  => 'iterateCallback',
            ]
        ];
    }
}
