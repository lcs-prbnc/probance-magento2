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
use Probance\M2connector\Model\Flow\Formater\CatalogProductFormater;
use Probance\M2connector\Model\ResourceModel\MappingProduct\CollectionFactory as ProductMappingCollectionFactory;

class CatalogProduct extends AbstractFlow
{
    /**
     * Suffix use for filename defined configuration path
     */
    const EXPORT_CONF_FILENAME_SUFFIX = '_product';

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
     * @var CatalogProductFormater
     */
    protected $catalogProductFormater;

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
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var int
     */
    protected $exportStore;

    /**
     * CatalogProduct constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator

     * @param ProductMappingCollectionFactory $productMappingCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param CatalogProductFormater $catalogProductFormater
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

        ProductMappingCollectionFactory $productMappingCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        CatalogProductFormater $catalogProductFormater,
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

        $this->flowMappingCollectionFactory = $productMappingCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->catalogProductFormater = $catalogProductFormater;
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
                $this->catalogProductFormater->setExportStore($this->exportStore);
            } else {
                $product = $this->productRepository->getById($entity->getId());
                $this->catalogProductFormater->setExportStore(Store::DEFAULT_STORE_ID);
            }
            $parent = $this->configurable->getParentIdsByChild($product->getId());
        } catch (NoSuchEntityException $e) {
            return;
        }

        if (!isset($parent[0])) {
            try {
                if ($this->progressBar) {
                    $this->progressBar->setMessage(__('Processing: %1', $product->getSku()), 'status');
                }
                foreach ($this->mapping['items'] as $mappingItem) {
                    $key = $mappingItem['magento_attribute'];
                    $dataKey = $key . '-' . $mappingItem['position'];
                    $method = 'get' . $this->catalogProductFormater->convertToCamelCase($key);

                    $data[$dataKey] = '';

                    if (!empty($mappingItem['user_value'])) {
                        $data[$dataKey] = $mappingItem['user_value'];
                        continue;
                    }
                    if (method_exists($this->catalogProductFormater, $method)) {
                        $data[$dataKey] = $this->catalogProductFormater->$method($product);
                    } else if (method_exists($product, $method)) {
                        $data[$dataKey] = $product->$method();
                    } else {
                        $customAttribute = $product->getCustomAttribute($key);
                        if ($customAttribute) {
                            $data[$dataKey] = $this->formatValueWithRenderer($key, $product);
                        }
                    }

                    $escaper = [
                        '~'.$this->probanceHelper->getFlowFormatValue('enclosure').'~'
                        => $this->probanceHelper->getFlowFormatValue('escape').$this->probanceHelper->getFlowFormatValue('enclosure')
                    ];

                    $data[$dataKey] = $this->typeFactory
                        ->getInstance($mappingItem['field_type'])
                        ->render($data[$dataKey], $mappingItem['field_limit'], $escaper);
                }

                $this->file->filePutCsv(
                    $this->csv,
                    $this->probanceHelper->postProcessData($data),
                    $this->probanceHelper->getFlowFormatValue('field_separator'),
                    $this->probanceHelper->getFlowFormatValue('enclosure')
                );

            } catch (\Exception $e) {
                $this->errors[] = [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ];
            }
        }
        unset($product);
        unset($parent);
                
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
