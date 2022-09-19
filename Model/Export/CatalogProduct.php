<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\Product\Attribute\Repository as EavRepository;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\LogFactory;
use Probance\M2connector\Model\Flow\Renderer\Factory as RendererFactory;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Probance\M2connector\Model\Flow\Formater\CatalogProductFormater;
use Probance\M2connector\Model\ResourceModel\MappingProduct\CollectionFactory as ProductMappingCollectionFactory;
use Psr\Log\LoggerInterface;

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
     * CatalogProduct constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     * @param LoggerInterface $logger

     * @param ProductMappingCollectionFactory $productMappingCollectionFactory
     * @param ProductCollection $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param CatalogProductFormater $catalogProductFormater
     * @param RendererFactory $rendererFactory
     * @param TypeFactory $typeFactory
     * @param EavRepository $eavRepository
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

        ProductMappingCollectionFactory $productMappingCollectionFactory,
        ProductCollection $productCollection,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        CatalogProductFormater $catalogProductFormater,
        RendererFactory $rendererFactory,
        TypeFactory $typeFactory,
        EavRepository $eavRepository
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

        $this->flowMappingCollectionFactory = $productMappingCollectionFactory;
        $this->productCollection = $productCollection;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->catalogProductFormater = $catalogProductFormater;
        $this->rendererFactory = $rendererFactory;
        $this->typeFactory = $typeFactory;
        $this->eavRepository = $eavRepository;
    }

    /**
     * @param $args
     */
    public function iterateCallback($args)
    {
        try {
            $product = $this->productRepository->getById($args['row']['entity_id']);
            $parent = $this->configurable->getParentIdsByChild($product->getId());
        } catch (NoSuchEntityException $e) {
            return;
        }

        if (!isset($parent[0])) {
            try {
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
                    $data,
                    $this->probanceHelper->getFlowFormatValue('field_separator'),
                    $this->probanceHelper->getFlowFormatValue('enclosure')
                );

                if ($this->progressBar) {
                    $this->progressBar->setMessage('Processing: ' . $product->getSku(), 'status');
                    $this->progressBar->advance();
                }
            } catch (\Exception $e) {
                $this->errors[] = [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ];
            }
        }
        unset($product);
        unset($parent);
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

        $this->productCollection->addAttributeToFilter('status', Status::STATUS_ENABLED);

        return [
            [
                'object' => $this->productCollection,
                'callback' => 'iterateCallback',
            ]
        ];
    }
}
