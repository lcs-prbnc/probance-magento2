<?php
namespace Probance\M2connector\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class UpgradeV165Patch
    implements DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();

        $connection->startSetup();
       
        $tableProduct = $connection->getTableName('probance_mapping_product');
        $tableArticle = $connection->getTableName('probance_mapping_article');
        for ($i=1; $i<=4; $i++) {
            $query = "UPDATE " . $tableProduct . " set magento_attribute='category".$i."##name' where magento_attribute='category".$i."'";
            $connection->query($query); 
            $query = "UPDATE " . $tableArticle . " set magento_attribute='category".$i."##name' where magento_attribute='category".$i."'";
            $connection->query($query); 
        }

        $connection->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
        ];
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}

