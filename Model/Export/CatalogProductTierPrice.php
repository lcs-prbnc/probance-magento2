<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Probance\M2connector\Model\BatchIterator as Iterator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Probance\M2connector\Model\Flow\Formater\CatalogProductFormater;
use Probance\M2connector\Model\ResourceModel\MappingProduct\CollectionFactory as ProductMappingCollectionFactory;
use Probance\M2connector\Model\ResourceModel\MappingProductTierPrice\CollectionFactory as ProductTierPriceMappingCollectionFactory;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CatalogProductTierPrice extends CatalogProduct
{
    /**
     * Suffix use for filename defined configuration path
     */
    const EXPORT_CONF_FILENAME_SUFFIX = '_product_tier_price';

    /**
     * @var array
     */
    protected $processedProducts = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * CatalogProductTierPrice constructor.
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
     * @param TypeFactory $typeFactory
     * @param ProductFactory $productFactory

     * @param ProductTierPriceMappingCollectionFactory $productTierPriceMappingCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
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
        TypeFactory $typeFactory,
        ProductFactory $productFactory,

        ProductTierPriceMappingCollectionFactory $productTierPriceMappingCollectionFactory,
        ScopeConfigInterface $scopeConfig
    )
    {
        parent::__construct(
            $probanceHelper,
            $directoryList,
            $file,
            $ftp,
            $iterator,

            $productMappingCollectionFactory,
            $productCollectionFactory,
            $productRepository,
            $configurable,
            $catalogProductFormater,
            $typeFactory,
            $productFactory
        );

        $this->flowMappingCollectionFactory = $productTierPriceMappingCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $entity
     */
    public function iterateCallback($entity)
    {
        try {
            $product = $this->productRepository->getById($entity->getId());
            $parent = $this->configurable->getParentIdsByChild($product->getId());
        } catch (NoSuchEntityException $e) {
            return;
        }

        if (!isset($parent[0]) && !in_array($product->getId(), $this->processedProducts)) {
            if ($this->progressBar) {
                $this->progressBar->setMessage(__('Processing: %1', $product->getSku()), 'status');
            }
            $tierPrices = $product->getTierPrices();
            if (!$tierPrices) $tierPrices = [];
            foreach ($tierPrices as $tierPrice) {
                try {
                    $this->catalogProductFormater->setFlowTierPrice($tierPrice);
                    $data = [];

                    foreach ($this->mapping['items'] as $mappingItem) {
                        $key = $mappingItem['magento_attribute'];
                        $dataKey = $key . '-' . $mappingItem['position'];
                        list($key, $subAttribute) = $this->getSubAttribute($key);
                        $method = 'get' . $this->catalogProductFormater->convertToCamelCase($key);

                        $data[$dataKey] = '';

                        if (!empty($mappingItem['user_value'])) {
                            $data[$dataKey] = $mappingItem['user_value'];
                            continue;
                        }
                        if (method_exists($this->catalogProductFormater, $method)) {
                            if ($subAttribute) $data[$dataKey] = $this->catalogProductFormater->$method($product, $subAttribute);
                            else $data[$dataKey] = $this->catalogProductFormater->$method($product);
                        } else if (method_exists($product, $method)) {
                            $data[$dataKey] = $product->$method();
                        } else {
                            $customAttribute = $product->getCustomAttribute($key);
                            if ($customAttribute) {
                                $data[$dataKey] = $this->catalogProductFormater->formatValueWithRenderer($key, $product);
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

                    @fputcsv(
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

            $this->processedProducts[] = $product->getId();
        }
        unset($product);
        unset($parent);

        if ($this->progressBar) {
            $this->progressBar->advance();
        }
    }

}
