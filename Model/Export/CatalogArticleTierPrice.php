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
use Magento\Catalog\Model\Product\Attribute\Repository as EavRepository;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\Flow\Renderer\Factory as RendererFactory;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Probance\M2connector\Model\Flow\Formater\CatalogArticleFormater;
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
     * CatalogArticleTierPrice constructor.
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

        ArticleMappingCollectionFactory $articleMappingCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        CatalogArticleFormater $catalogArticleFormater,
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

            $articleMappingCollectionFactory,
            $productCollectionFactory,
            $productRepository,
            $configurable,
            $catalogArticleFormater,
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
     * @param $entity
     */
    public function iterateCallback($entity)
    {
        try {
            $product = $this->productRepository->getById($entity->getId());

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
                if ($this->progressBar) {
                    $this->progressBar->setMessage('Processing: ' . $child->getSku(), 'status');
                }
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

                            if ($this->scopeConfig->getValue('tax/calculation/price_includes_tax', ScopeInterface::SCOPE_STORE, $product->getStoreId())) {
                                $priceExcludingTax = $regularPrice / (1 + ($rate / 100));
                            } else {
                                $priceExcludingTax = $regularPrice;
                            }

                            $priceIncludingTax = $priceExcludingTax + ($priceExcludingTax * ($rate / 100));
                        }

                        $data = [
                            $child->getId(),
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
                    $this->progressBar->advance();
                }

                $this->processedProducts[] = $product->getId();
            }
        }
        unset($product);
        unset($childs);
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
