<?php

namespace Probance\M2connector\Model\Flow\Formater;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Module\Manager as ModuleManager;

class InventoryFormater extends AbstractFormater
{
    protected $objectManager;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
    * @var GetStockItemConfigurationInterface
    */
    protected $getStockItemConfiguration;

    /**
    * @var StockByWebsiteIdResolver
    */
    protected $stockByWebsiteIdResolver;

    /**
    * @var GetProductSalableQty
    */
    protected $getProductSalableQty;

    /**
     * CatalogProductFormater constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ModuleManager $moduleManager,
    )
    {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Check if MSI module enable.
     * Use ObjectManager cause namespace use disable compilation if MSI replaced with * in composer
     * @return bool
     * @throws
     */
    public function isUsable():bool
    {   
        if ($this->moduleManager->isEnabled('Magento_InventoryConfigurationApi')) {
            // Ensure not a app/etc/config.php error....
            if (class_exists('\Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface')) {
                $this->stockByWebsiteIdResolver = $this->objectManager->get(\Magento\InventorySales\Model\StockByWebsiteIdResolver::class);
                $this->getStockItemConfiguration = $this->objectManager->get(\Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface::class);
                $this->getProductSalableQty = $this->objectManager->get(\Magento\InventorySales\Model\GetProductSalableQty::class);
                return true;
            } else {
                throw new \Exception('Magento_InventoryConfigurationApi is enabled but classes not found !');
            } 
        }
        return false;
    }

    /**
     * Retrieve stockItem using MSI
     */
    public function getStockItem($sku, $websiteId) 
    {
        $stock = $this->stockByWebsiteIdResolver->execute($websiteId);
        $stockId = $stock->getStockId();
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $isManageStock = $stockItemConfiguration->isManageStock();
        $stockItem->setData('manage_stock', $isManageStock);
        $qty = $isManageStock ? $this->getProductSalableQty->execute($sku, $stockId) : 0;
        $stockItem->setData('qty', $qty);
        $stockItem->setData('is_in_stock', ($qty > 0));
    }
}
