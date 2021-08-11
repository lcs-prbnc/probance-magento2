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


class CatalogProductFormater extends AbstractFormater
{
    /**
     * Path to tax configuration
     */
    const XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX = 'tax/calculation/price_includes_tax';

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var TaxCalculationInterface
     */
    private $taxCalculation;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    protected $stockItemRepository;
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
     */
    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        Configurable $configurable,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Emulation $appEmulation,
        TaxCalculationInterface $taxCalculation,
        ScopeConfigInterface $scopeConfig,
        StockItemRepository $stockItemRepository
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
        if ($this->scopeConfig->getValue(self::XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE)) {
            return $product->getSpecialPrice();
        }

        $priceExclTax = $this->getSpecialPriceExclTax($product);
        return $priceExclTax + ($priceExclTax * ($this->getTaxRate($product) / 100));
    }

    /**
     * Retrieve special price excluding tax
     *
     * @param ProductInterface $product
     * @return float|int|\Magento\Framework\Api\AttributeInterface|null
     */
    public function getSpecialPriceExclTax(ProductInterface $product)
    {
        if ($this->scopeConfig->getValue(self::XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE)) {
            return $product->getSpecialPrice() / (1 + ($this->getTaxRate($product) / 100));
        }

        return $product->getSpecialPrice();
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
     * Retrieve product base image URL
     *
     * @param ProductInterface $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getImageUrl(ProductInterface $product)
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . 'catalog/product' . $product->getImage()
        ;
    }
    /*public function getStockItem($productId)
    {
        return $this->stockItemRepository->get($productId);
    }*/

    public function getIsInStock(ProductInterface $product)
    {
        
       if ($this->stockItemRepository->get($product->getId())->getIsInStock() == 1){
        return "Product is Available";
       }
       else{
        return "Product isnot Available";
       }
        
    }
    public function getQty(ProductInterface $product)
    {
        
       return $this->stockItemRepository->get($product->getId())->getQty();
        
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
    private function getCategory($categoryId)
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
    private function getTaxRate(ProductInterface $product)
    {
        $rate = 0;

        if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
            $productRateId = $taxAttribute->getValue();
            $rate = $this->taxCalculation->getCalculatedRate($productRateId);
        }

        return $rate;
    }
}
