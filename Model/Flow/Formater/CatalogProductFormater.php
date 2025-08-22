<?php

namespace Probance\M2connector\Model\Flow\Formater;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Repository\Proxy as EavRepository;
use Magento\Catalog\Model\Category\AttributeRepository\Proxy as CategoryEavRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Probance\M2connector\Model\Flow\Renderer\Factory as RendererFactory;

class CatalogProductFormater extends AbstractFormater
{
    /** 
     * Path to tax configuration
     */
    const XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX = 'tax/calculation/price_includes_tax';

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var Emulation
     */
    protected $appEmulation;

    /**
     * @var TaxCalculationInterface
     */
    protected $taxCalculation;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $exportStore;

    /**
     * @var \Magento\Catalog\Api\Data\ProductTierPriceInterface 
     */
    protected $productFlowTierPrice;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var EavRepository
     */
    protected $eavRepository;

    /**
     * @var CategoryEavRepository
     */
    protected $categoryEavRepository;

    /**
     * @var RendererFactory
     */
    protected $rendererFactory;

    /**
     * CatalogProductFormater constructor.
     *
     * @param LoggerInterface $logger
     * @param CollectionFactory $categoryCollectionFactory
     * @param Configurable $configurable
     * @param StoreManagerInterface $storeManager
     * @param BlockFactory $blockFactory
     * @param Emulation $appEmulation
     * @param TaxCalculationInterface $taxCalculation
     * @param ScopeConfigInterface $scopeConfig
     * @param StockItemRepository $stockItemRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param EavRepository $eavRepository
     * @param CategoryEavRepository $categoryEavRepository
     * @param RendererFactory $rendererFactory
     */
    public function __construct(
        LoggerInterface $logger,
        CollectionFactory $categoryCollectionFactory,
        Configurable $configurable,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Emulation $appEmulation,
        TaxCalculationInterface $taxCalculation,
        ScopeConfigInterface $scopeConfig,
        StockItemRepository $stockItemRepository,
        GroupRepositoryInterface $groupRepository,
        EavRepository $eavRepository,
        CategoryEavRepository $categoryEavRepository,
        RendererFactory $rendererFactory
    )
    {
        $this->logger = $logger;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->configurable = $configurable;
        $this->storeManager = $storeManager;
        $this->blockFactory = $blockFactory;
        $this->appEmulation = $appEmulation;
        $this->taxCalculation = $taxCalculation;
        $this->scopeConfig = $scopeConfig;
        $this->stockItemRepository = $stockItemRepository;
        $this->groupRepository = $groupRepository;
        $this->eavRepository = $eavRepository;
        $this->categoryEavRepository = $categoryEavRepository;
        $this->rendererFactory = $rendererFactory;
    }

    /**
     * Retrieve price including tax
     *
     * @param ProductInterface $product
     * @return float|int|null
     */
    public function getPriceInclTax(ProductInterface $product)
    {
        if ($this->scopeConfig->getValue(self::XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE, $product->getStoreId())) {
            return $product->getPrice();
        }

        $priceExclTax = $this->getPriceExclTax($product);
        return $priceExclTax + ($priceExclTax * ($this->getTaxRate($product) / 100));
    }

    /**
     * Get price excluding tax
     *
     * @param ProductInterface $product
     * @return float|int|null
     */
    public function getPriceExclTax(ProductInterface $product)
    {
        if ($this->scopeConfig->getValue(self::XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE, $product->getStoreId())) {
            return $product->getPrice() / (1 + ($this->getTaxRate($product) / 100));
        }

        return $product->getPrice();
    }

    /**
     * Check if special price available
     *
     * @param ProductInterface $product
     * @return boolean
     */
    public function hasSpecialPriceValid(ProductInterface $product)
    {
        $result = false;
        $specialfromdate = $product->getSpecialFromDate();
        $specialtodate = $product->getSpecialToDate();
        $today = time();
        if ((is_null($specialfromdate) && is_null($specialtodate)) || 
            (!is_null($specialfromdate) && $today >= strtotime($specialfromdate) && is_null($specialtodate)) || 
            (!is_null($specialtodate) && $today <= strtotime($specialtodate) && is_null($specialfromdate)) || 
            (!is_null($specialfromdate) && !is_null($specialtodate) && $today >= strtotime($specialfromdate) && $today <= strtotime($specialtodate))) 
        {
            $result = true;
        }
        return $result;
    }

    /**
     * Retrieve special price including tax
     *
     * @param ProductInterface $product
     * @return float|int|\Magento\Framework\Api\AttributeInterface|null
     */
    public function getSpecialPriceInclTax(ProductInterface $product)
    {
        $specialPrice = '';
        if ($this->hasSpecialPriceValid($product)) {
            if ($this->scopeConfig->getValue(self::XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE, $product->getStoreId())) {
                $specialPrice = $this->getSpecialPrice($product);
            } else {
                $priceExclTax = $this->getSpecialPriceExclTax($product);
                if ($priceExclTax) $specialPrice = $priceExclTax + ($priceExclTax * ($this->getTaxRate($product) / 100));
            }
        }
        return $specialPrice;
    }

    /**
     * Retrieve special price excluding tax
     *
     * @param ProductInterface $product
     * @return float|int|\Magento\Framework\Api\AttributeInterface|null
     */
    public function getSpecialPriceExclTax(ProductInterface $product)
    {
        $specialPrice = '';
        if ($this->hasSpecialPriceValid($product)) {
            $specialPrice = $this->getSpecialPrice($product);
            if ($this->scopeConfig->getValue(self::XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE, $product->getStoreId()) && $specialPrice) {
                $specialPrice = $specialPrice / (1 + ($this->getTaxRate($product) / 100));
            }
        }
        return $specialPrice;
    }

    /**
     * Check special price with case finalPrice < regularPrice
     *
    */
    public function getSpecialPrice($product)
    {
        $specialPrice = $product->getSpecialPrice();
        $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();
        if (!$specialPrice && ($finalPrice < $regularPrice)) {
            $specialPrice = $this->catalogHelper->getTaxPrice($product, $finalPrice, true);
        } else if ($specialPrice && ($finalPrice < $specialPrice)) {
            $specialPrice = '';
        }

        if (!$specialPrice) $specialPrice = '';

        return $specialPrice;
    }

    /**
     * Get all possible categories paths
     *
     * @param ProductInterface $product
     * @return array
     */
    public function getAllCategoriesPath(ProductInterface $product)
    {
        $categories = [];

        $categoryIds = $product->getCategoryIds();

        $categoryPaths = [];
        foreach ($categoryIds as $categoryId) 
        {
            $category = $this->getCategory($categoryId);
            if ($category->getId()) {
                $path = $category->getPath();
                foreach ($categoryPaths as $k => $categoryPath) {
                    if (strpos($categoryPath . '/', $path . '/') === 0) {
                        continue 2;
                    }
                    if (strpos($path . '/', $categoryPath . '/') === 0) {
                        $categoryPaths[$k] = $path;
                        continue 2;
                    }
                }

                $categoryPaths[] = $path;
            }
        }

        sort($categoryPaths);

        return $categoryPaths;
    }

    /**
     * Get all possible categories names as paths
     *
     * @param ProductInterface $product
     * @return array
     */
    public function getCategoriesName(ProductInterface $product)
    {
        $categoryPaths = $this->getAllCategoriesPath($product);
        foreach ($categoryPaths as $k => $categoryPath) {
            $categoryIds = explode('/', $categoryPath);
            $categoryNames = [];
            foreach ($categoryIds as $categoryId) {
                $category = $this->getCategory($categoryId);
                if ($category->getLevel() > 0) {
                    $categoryNames[] = $category->getName();
                }
            }

            $categoryPaths[$k] = implode('/', $categoryNames);
        }

        return implode('|', $categoryPaths);
    }

    /**
     * Get all categories from path, keeping last possible path
     * @param ProductInterface $product
     * @return array
     */
    public function getCategories(ProductInterface $product)
    {            
        $categoryPaths = $this->getAllCategoriesPath($product);
        // Case multiple tree categories
        if (count($categoryPaths) > 1) $categoryPath = end($categoryPaths);
        // Case only one path
        elseif (isset($categoryPaths[0])) $categoryPath = $categoryPaths[0];

        $categoryIds = explode('/', $categoryPath);
        foreach ($categoryIds as $categoryId) 
        {
            $categories[] = $this->getCategory($categoryId);
        }

        return $categories;
    }

    /**
     * Get category attribute given level and submethod for product
     *
     * @param int $level
     * @param ProductInterface $product
     * @param string $subAttribute
     * @return string
     * @throws
     */
    public function getCategoryX($level, ProductInterface $product, $subAttribute)
    {
        $result = '';
        $categories = $this->getCategories($product);
        if (!empty($categories) && isset($categories[$level])) {
            $category = $categories[$level];
            $camelCaseMethod = $this->convertToCamelCase($subAttribute);
            $formatterMethod = 'getCategory'.$camelCaseMethod;
            $entityMethod = 'get'.$camelCaseMethod;
            if (method_exists($this, $formatterMethod)) {
                $result = $this->$formatterMethod($category);
            } else if (method_exists($category, $entityMethod)) {
                $result = $category->$entityMethod();
            } else {
                $customAttribute = $category->getCustomAttribute($subAttribute);
                if ($customAttribute) {
                    $result = $this->formatCategoryValueWithRenderer($subAttribute, $category);
                }
            }
        }
        return $result;
    }

    /**
     * Get category1 ## ???
     *
     * @param ProductInterface $product
     * @param string $subMethod
     * @return string
     * @throws
     */
    public function getCategory1(ProductInterface $product, $subMethod)
    {
        return $this->getCategoryX(1, $product, $subMethod);
    }

    /**
     * Get category2 ## ???
     *
     * @param ProductInterface $product
     * @param string $subMethod
     * @return string
     * @throws
     */
    public function getCategory2(ProductInterface $product, $subMethod)
    {
        return $this->getCategoryX(2, $product, $subMethod);
    } 

    /**
     * Get category3 ## ???
     *
     * @param ProductInterface $product
     * @param string $subMethod
     * @return string
     * @throws
     */
    public function getCategory3(ProductInterface $product, $subMethod)
    {
        return $this->getCategoryX(3, $product, $subMethod);
    }

    /**
     * Get category4 ## ???
     *
     * @param ProductInterface $product
     * @param string $subMethod
     * @return string
     * @throws
     */
    public function getCategory4(ProductInterface $product, $subMethod)
    {
        return $this->getCategoryX(4, $product, $subMethod);
    }

    /**
     * Retrieve product base image URL
     *
     * @param ProductInterface $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getImageUrl(ProductInterface $product)
    {
        return $this->storeManager->getStore($this->exportStore)->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/product' . $product->getImage();
    }

    public function getStockItem($product)
    {
        try {
            return $product->getExtensionAttributes()->getStockItem();//$this->stockItemRepository->get($productId);
        } catch (\Exception $e) {
            $this->logger->error('Stock item not found for product '.$productId.' :: '.$e->getMessage());
        }
    }

    public function getIsInStock(ProductInterface $product)
    {
        if ($stockItem = $this->getStockItem($product)) {
            if ($stockItem->getIsInStock()) {
                return 1;
            }
        }
        return 0;
    }

    public function getManageStock(ProductInterface $product)
    {
        if ($stockItem = $this->getStockItem($product)) {
            if ($stockItem->getManageStock()) {
                return 1;
            }
        }
        return 0;
    }

    public function getQty(ProductInterface $product)
    {
        if ($stockItem = $this->getStockItem($product)) {
            if ($stockItem->getIsInStock()) {
                return $stockItem->getQty();
            }
        }
        return 0;
    }

    /**
     * Retrieve Parent ID (Configurable Product ID)
     *
     * @param ProductInterface $product
     * @return int|null
     */
    public function getParentId(ProductInterface $product)
    {
        $ids = $this->configurable->getParentIdsByChild($product->getId());
        return isset($ids[0]) ? $ids[0] : $product->getId();
    }

    /**
     * Get category by id
     *
     * @param $categoryId
     * @return DataObject
     * @throws LocalizedException
     */
    protected function getCategory($categoryId)
    {
        $category = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToSelect(['name', 'path', 'level'])
            ->addAttributeToFilter('entity_id', $categoryId);

        return $category->getFirstItem();
    }

    /**
     * Retrieve tax rate
     *
     * @param ProductInterface $product
     * @return float|int
     */
    protected function getTaxRate(ProductInterface $product)
    {
        $rate = 0;

        if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
            $productRateId = $taxAttribute->getValue();
            $rate = $this->taxCalculation->getCalculatedRate($productRateId);
        }

        return $rate;
    }

    public function setExportStore($storeId)
    {
        $this->exportStore = $storeId;
    }

    public function getLocale(ProductInterface $product)
    {
        $locale = $this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORES, $product->getStoreId());
        if (empty($locale)) $locale = 'fr_FR';

        return $locale;
    }

    public function setFlowTierPrice($tierPrice)
    {
        $this->productFlowTierPrice = $tierPrice;
    }

    public function getTierPriceCustomerGroupId(ProductInterface $product)
    {
        $customerGroupId = '';
        if ($this->productFlowTierPrice) {
            if ($this->productFlowTierPrice->getCustomerGroupId() != GroupInterface::CUST_GROUP_ALL) {
                $customerGroupId = $this->productFlowTierPrice->getCustomerGroupId();
            }
        }
        return $customerGroupId;
    }

    public function getTierPriceCustomerGroupCode(ProductInterface $product)
    {
        $customerGroupCode = 'ALL GROUPS';
        if ($this->productFlowTierPrice) {
            $customerGroupId = $this->productFlowTierPrice->getCustomerGroupId();
            if ($customerGroupId != GroupInterface::CUST_GROUP_ALL) {
                $customerGroup = $this->groupRepository->getById($customerGroupId);
                $customerGroupCode = $customerGroup->getCode();
            }
        }
        return $customerGroupCode;
    }

    public function getTierPriceValue(ProductInterface $product)
    {
        $value = '';
        if ($this->productFlowTierPrice) {
            $value = $this->productFlowTierPrice->getValue();
        }
        return $value;
    }

    /**
     * Format value
     *
     * @param $code
     * @param ProductInterface|CategoryInterface $entity
     * @param null|CategoryEavRepository $eavRepository
     * @return string
     */
    public function formatValueWithRenderer($code, $entity, $eavRepository = null)
    {
        $value = '';

        try {
            if (!$eavRepository) $eavRepository = $this->eavRepository;
            $eavAttribute = $eavRepository->get($code);
            $value = $this->rendererFactory
                ->getInstance($eavAttribute->getFrontendInput())
                ->render($entity, $eavAttribute);
        } catch (NoSuchEntityException $e) {

        }

        return $value;
    }

    public function formatCategoryValueWithRenderer($code, CategoryInterface $entity) 
    {
        return $this->formatValueWithRenderer($code, $entity, $this->categoryEavRepository);
    }
}
