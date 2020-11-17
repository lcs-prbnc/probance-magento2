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
use Probance\M2connector\Model\ResourceModel\MappingArticle\CollectionFactory as ArticleMappingCollectionFactory;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Api\TaxCalculationInterface;

class CatalogArticleTierPrice extends CatalogArticle
{
    /**
     * Suffix use for filename defined configuration path
     */
    const EXPORT_CONF_FILENAME_SUFFIX = '_article_tier_price';

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
     * @var TaxCalculationInterface
     */
    private $taxCalculation;

    /**
     * CatalogArticleTierPrice constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository

     * @param ArticleMappingCollectionFactory $articleMappingCollectionFactory
     * @param ProductCollection $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param CatalogProductFormater $catalogProductFormater
     * @param RendererFactory $rendererFactory
     * @param TypeFactory $typeFactory
     * @param EavRepository $eavRepository

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
        LogFactory $logFactory,
        LogRepositoryInterface $logRepository,

        ArticleMappingCollectionFactory $articleMappingCollectionFactory,
        ProductCollection $productCollection,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        CatalogProductFormater $catalogProductFormater,
        RendererFactory $rendererFactory,
        TypeFactory $typeFactory,
        EavRepository $eavRepository,

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
            $logFactory,
            $logRepository,

            $articleMappingCollectionFactory,
            $productCollection,
            $productRepository,
            $configurable,
            $catalogProductFormater,
            $rendererFactory,
            $typeFactory,
            $eavRepository
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

            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                $childs = $this->configurable->getUsedProducts($product);
            } else {
                $childs = [$product];
            }
        } catch (NoSuchEntityException $e) {
            return;
        }

        foreach ($childs as $child) {
            if (!in_array($child->getId(), $this->processedProducts)) {
                foreach ($child->getTierPrices() as $tierPrice) {
                    try {
                        $customerGroupId = '';
                        $customerGroupCode = 'ALL GROUPS';
                        if ($tierPrice->getCustomerGroupId() != GroupInterface::CUST_GROUP_ALL) {
                            $customerGroupId = $tierPrice->getCustomerGroupId();
                            $customerGroup = $this->groupRepository->getById($customerGroupId);
                            $customerGroupCode = $customerGroup->getCode();
                        }

                        $regularPrice = $child->getPriceInfo()->getPrice('regular_price')->getValue();

                        $priceIncludingTax = $priceExcludingTax = $regularPrice;

                        if ($taxAttribute = $child->getCustomAttribute('tax_class_id')) {
                            $productRateId = $taxAttribute->getValue();
                            $rate = $this->taxCalculation->getCalculatedRate($productRateId);

                            if ($this->scopeConfig->getValue('tax/calculation/price_includes_tax', ScopeInterface::SCOPE_STORE)) {
                                $priceExcludingTax = $regularPrice / (1 + ($rate / 100));
                            } else {
                                $priceExcludingTax = $regularPrice;
                            }

                            $priceIncludingTax = $priceExcludingTax + ($priceExcludingTax * ($rate / 100));
                        }

                        $data = [
                            'article_id' => $child->getId(),
                            'article_group_prix' => $customerGroupCode,
                            'id_group' => $customerGroupId,
                            'date_promo' => '',
                            'prix_ttc' => round($priceIncludingTax, 2),
                            'prix_promo' => $tierPrice->getValue(),
                            'prix_ht' => round($priceExcludingTax, 2)
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
                            'trace' => $e->getTrace(),
                        ];
                    }
                }

                if ($this->progressBar) {
                    $this->progressBar->setMessage('Processing: ' . $product->getSku(), 'status');
                    $this->progressBar->advance();
                }

                $this->processedProducts[] = $product->getId();
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
            'article_id',
            'article_group_prix',
            'id_group',
            'date_promo',
            'prix_ttc',
            'prix_promo',
            'prix_ht'
        ];
    }
}
