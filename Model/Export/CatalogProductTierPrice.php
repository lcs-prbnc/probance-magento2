<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Repository as EavRepository;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\Flow\Renderer\Factory as RendererFactory;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Probance\M2connector\Model\Flow\Formater\CatalogProductFormater;
use Probance\M2connector\Model\ResourceModel\MappingProduct\CollectionFactory as ProductMappingCollectionFactory;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Api\TaxCalculationInterface;

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
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var TaxCalculationInterface
     */
    protected $taxCalculation;

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
     * @param RendererFactory $rendererFactory
     * @param TypeFactory $typeFactory
     * @param EavRepository $eavRepository
     * @param ProductFactory $productFactory

     * @param ScopeConfigInterface $scopeConfig
     * @param GroupRepositoryInterface $groupRepository
     * @param TaxCalculationInterface $taxCalculation
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
        ProductFactory $productFactory,

        ScopeConfigInterface $scopeConfig,
        GroupRepositoryInterface $groupRepository,
        TaxCalculationInterface $taxCalculation
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
            $rendererFactory,
            $typeFactory,
            $eavRepository,
            $productFactory
        );

        $this->scopeConfig = $scopeConfig;
        $this->groupRepository = $groupRepository;
        $this->taxCalculation = $taxCalculation;
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

        if (!isset($parent[0]) && !in_array($product->getId(), $this->processedProducts)) {
            foreach ($product->getTierPrices() as $tierPrice) {
                try {
                    $customerGroupId = '';
                    $customerGroupCode = 'ALL GROUPS';
                    if ($tierPrice->getCustomerGroupId() != GroupInterface::CUST_GROUP_ALL) {
                        $customerGroupId = $tierPrice->getCustomerGroupId();
                        $customerGroup = $this->groupRepository->getById($customerGroupId);
                        $customerGroupCode = $customerGroup->getCode();
                    }

                    $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();

                    $priceIncludingTax = $priceExcludingTax = $regularPrice;

                    if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
                        $productRateId = $taxAttribute->getValue();
                        $rate = $this->taxCalculation->getCalculatedRate($productRateId);

                        if ($this->scopeConfig->getValue('tax/calculation/price_includes_tax', ScopeInterface::SCOPE_STORE, $product->getStoreId())) {
                            $priceExcludingTax = $regularPrice / (1 + ($rate / 100));
                        } else {
                            $priceExcludingTax = $regularPrice;
                        }

                        $priceIncludingTax = $priceExcludingTax + ($priceExcludingTax * ($rate / 100));
                    }

                    $data = [
                        $product->getId(),
                        $customerGroupCode,
                        $customerGroupId,
                        '',
                        round($priceIncludingTax, 2),
                        $tierPrice->getValue(),
                        round($priceExcludingTax, 2)
                    ];

                    $this->file->filePutCsv(
                        $this->csv,
                        $data,
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

            if ($this->progressBar) {
                $this->progressBar->setMessage('Processing: ' . $product->getSku(), 'status');
                $this->progressBar->advance();
            }

            $this->processedProducts[] = $product->getId();
        }
        unset($product);
        unset($parent);
    }

    /**
     * Get header data
     *
     * @return array
     */
    public function getHeaderData()
    {
        return [
            'product_id',
            'product_group_prix',
            'id_group',
            'date_promo',
            'prix_ttc',
            'prix_promo',
            'prix_ht'
        ];
    }
}
