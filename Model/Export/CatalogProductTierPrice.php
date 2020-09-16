<?php

namespace Walkwizus\Probance\Model\Export;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Walkwizus\Probance\Model\Ftp;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Tax\Api\TaxCalculationInterface;
use Walkwizus\Probance\Model\LogFactory;
use Walkwizus\Probance\Api\LogRepositoryInterface;

class CatalogProductTierPrice extends AbstractCatalogProduct
{
    /**
     * @var array
     */
    private $processedProducts = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var TaxCalculationInterface
     */
    private $taxCalculation;

    /**
     * CatalogProductTierPrice constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param Iterator $iterator
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param ScopeConfigInterface $scopeConfig
     * @param GroupRepositoryInterface $groupRepository
     * @param ProductCollection $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param TaxCalculationInterface $taxCalculation
     * @param Configurable $configurable
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        Iterator $iterator,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        ScopeConfigInterface $scopeConfig,
        GroupRepositoryInterface $groupRepository,
        ProductCollection $productCollection,
        ProductRepositoryInterface $productRepository,
        TaxCalculationInterface $taxCalculation,
        Configurable $configurable,
        LogFactory $logFactory,
        LogRepositoryInterface $logRepository
    )
    {
        parent::__construct(
            $probanceHelper,
            $directoryList,
            $file,
            $ftp,
            $iterator,
            $productCollection,
            $logFactory,
            $logRepository
        );

        $this->scopeConfig = $scopeConfig;
        $this->groupRepository = $groupRepository;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
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
            try {
                foreach ($product->getTierPrices() as $tierPrice) {
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

                        if ($this->scopeConfig->getValue('tax/calculation/price_includes_tax', ScopeInterface::SCOPE_STORE)) {
                            $priceExcludingTax = $regularPrice / (1 + ($rate / 100));
                        } else {
                            $priceExcludingTax = $regularPrice;
                        }

                        $priceIncludingTax = $priceExcludingTax + ($priceExcludingTax * ($rate / 100));
                    }

                    $this->file->filePutCsv(
                        $this->csv,
                        [
                            $product->getId(),
                            $customerGroupCode,
                            $customerGroupId,
                            '',
                            round($priceIncludingTax, 2),
                            $tierPrice->getValue(),
                            round($priceExcludingTax, 2),
                        ],
                        $this->probanceHelper->getFlowFormatValue('field_separator'),
                        $this->probanceHelper->getFlowFormatValue('enclosure')
                    );
                }

                if ($this->progressBar) {
                    $this->progressBar->setMessage('Processing: ' . $product->getSku(), 'status');
                    $this->progressBar->advance();
                }

                $this->processedProducts[] = $product->getId();
            } catch (FileSystemException $e) {

            } catch (\Exception $e) {
                $this->errors[] = [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTrace(),
                ];
            }
        }
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

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->probanceHelper->getCatalogFlowValue('filename_product_tier_price');
    }
}