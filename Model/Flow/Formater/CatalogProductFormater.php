<?php

namespace Probance\M2connector\Model\Flow\Formater;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Psr\Log\LoggerInterface;

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
     * CatalogProductFormater constructor.
     *
     * @param CollectionFactory $categoryCollectionFactory
     * @param Configurable $configurable
     * @param StoreManagerInterface $storeManager
     * @param BlockFactory $blockFactory
     * @param Emulation $appEmulation
     * @param TaxCalculationInterface $taxCalculation
     * @param ScopeConfigInterface $scopeConfig
     * @param StockItemRepository $stockItemRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        Configurable $configurable,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Emulation $appEmulation,
        TaxCalculationInterface $taxCalculation,
        ScopeConfigInterface $scopeConfig,
        StockItemRepository $stockItemRepository,
        LoggerInterface $logger
    )
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->configurable = $configurable;
        $this->storeManager = $storeManager;
        $this->blockFactory = $blockFactory;
        $this->appEmulation = $appEmulation;
        $this->taxCalculation = $taxCalculation;
        $this->scopeConfig = $scopeConfig;
        $this->stockItemRepository = $stockItemRepository;
        $this->logger = $logger;
    }

    /**
     * Retrieve price including tax
     *
     * @param ProductInterface $product
     * @return float|int|null
     */
    public function getPriceInclTax(ProductInterface $product)
    {
        if ($this->scopeConfig->getValue(self::XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE)) {
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
        if ($this->scopeConfig->getValue(self::XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE)) {
            return $product->getPrice() / (1 + ($this->getTaxRate($product) / 100));
        }

        return $product->getPrice();
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
        if ($this->scopeConfig->getValue(self::XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE)) {
            $specialPrice = $product->getSpecialPrice();
        } else {
            $priceExclTax = $this->getSpecialPriceExclTax($product);
            if ($priceExclTax) $specialPrice = $priceExclTax + ($priceExclTax * ($this->getTaxRate($product) / 100));
        }
        if (!$specialPrice) $specialPrice = '';
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
        $specialPrice = $product->getSpecialPrice();
        if ($this->scopeConfig->getValue(self::XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE) && $specialPrice) {
            $specialPrice = $specialPrice / (1 + ($this->getTaxRate($product) / 100));
        }
        if (!$specialPrice) $specialPrice = '';
        return $specialPrice;
    }

    /**
     * Retrieve special price excluding tax
     *
     * @param ProductInterface $product
     * @return int
     */
    public function getQuantityAndStockStatus(ProductInterface $product)
    {
        $statusAndQuantity = $product->getQuantityAndStockStatus();
        return !empty($statusAndQuantity) ? $statusAndQuantity['qty'] : 0;
    }

    /**
     * Get categories
     *
     * @param ProductInterface $product
     * @return string
     * @throws
     */
    public function getCategories(ProductInterface $product)
    {
        $categoryIds = $product->getCategoryIds();

        $categoryPaths = [];
        foreach ($categoryIds as $categoryId) {
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
     * Get categories
     *
     * @param ProductInterface $product
     * @return string
     * @throws
     */
    public function getCategory1(ProductInterface $product)
    {
        $categories = $this->getCategories($product);
        $categoriesArray = array_filter(explode('|', $categories));
        return $this->getCategoryLevel($categoriesArray, 1);
    }

    /**
     * Get categories
     *
     * @param ProductInterface $product
     * @return string
     * @throws
     */
    public function getCategory2(ProductInterface $product)
    {
        $categories = $this->getCategories($product);
        $categoriesArray = array_filter(explode('|', $categories));
        return $this->getCategoryLevel($categoriesArray, 2);
    } 

    /**
     * Get categories
     *
     * @param ProductInterface $product
     * @return string
     * @throws
     */
    public function getCategory3(ProductInterface $product)
    {
        $categories = $this->getCategories($product);
        $categoriesArray = array_filter(explode('|', $categories));
        return $this->getCategoryLevel($categoriesArray, 3);
    }

    public function getCategoryLevel($categoriesArray, $level)
    {
        if (!empty($categoriesArray)) {
            // Case multiple tree categories
            if (count($categoriesArray) > 1) {
                $categoryNames = array_filter(explode('/',$categoriesArray[($level-1)]));
                return end($categoryNames);
            } else if (isset($categoriesArray[0])) {
            // Case only one path
                $categoryNames = array_filter(explode('/',$categoriesArray[0]));
                if (isset($categoryNames[($level-1)])) {
                    return $categoryNames[($level-1)];
                }
            }
        }
        return '';
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
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
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
        $stockItem = $this->getStockItem($product);
        if ($stockItem = $this->getStockItem($product)) {
            if ($stockItem->getIsInStock()) {
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
}
