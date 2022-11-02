<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\Product\Attribute\Repository as EavRepository;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Model\Store;
use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\LogFactory;
use Probance\M2connector\Model\Flow\Renderer\Factory as RendererFactory;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Probance\M2connector\Model\Flow\Formater\CatalogArticleFormater;
use Probance\M2connector\Model\ResourceModel\MappingArticle\CollectionFactory as ArticleMappingCollectionFactory;
use Psr\Log\LoggerInterface;

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
     * @var ProductCollection
     */
    protected $productCollection;

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
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     * @param LoggerInterface $logger

     * @param ArticleMappingCollectionFactory $articleMappingCollectionFactory
     * @param ProductCollection $productCollection
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
        LogFactory $logFactory,
        LogRepositoryInterface $logRepository,
        LoggerInterface $logger,

        ArticleMappingCollectionFactory $articleMappingCollectionFactory,
        ProductCollection $productCollection,
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
            $iterator,
            $logFactory,
            $logRepository,
            $logger
        );

        $this->flowMappingCollectionFactory = $articleMappingCollectionFactory;
        $this->productCollection = $productCollection;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->catalogArticleFormater = $catalogArticleFormater;
        $this->rendererFactory = $rendererFactory;
        $this->typeFactory = $typeFactory;
        $this->eavRepository = $eavRepository;
        $this->productFactory = $productFactory;
    }

    /**
     * @param $args
     */
    public function iterateCallback($args)
    {
        try {
            $data = [];
            if ($this->exportStore != Store::DEFAULT_STORE_ID) {
                $product = $this->productFactory->create()->setStoreId($this->exportStore->getId())->load($args['row']['entity_id']);
                $this->catalogArticleFormater->setExportStore($this->exportStore);
            } else {
                $product = $this->productRepository->getById($args['row']['entity_id']);
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
                        $data,
                        $this->probanceHelper->getFlowFormatValue('field_separator'),
                        $this->probanceHelper->getFlowFormatValue('enclosure')
                    );


                    if ($this->progressBar) {
                        $this->progressBar->setMessage('Processing: ' . $product->getSku(), 'status');
                        $this->progressBar->advance();
                    }
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
     * @return array
     */
    public function getArrayCollection()
    {
        if (isset($this->range['from']) && isset($this->range['to'])) {
            $this->productCollection
                ->addAttributeToFilter('updated_at', ['from' => $this->range['from']])
                ->addAttributeToFilter('updated_at', ['to' => $this->range['to']]);
        }
        $store = $this->probanceHelper->getFlowFormatValue('default_export_store'); 
        if (!$store) $store = Store::DEFAULT_STORE_ID;
        $this->productCollection->addStoreFilter($store);

        $this->productCollection->addAttributeToFilter('status', Status::STATUS_ENABLED);

        return [
            [
                'object' => $this->productCollection,
                'callback' => 'iterateCallback',
            ]
        ];
    }
}
