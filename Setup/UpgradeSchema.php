<?php

namespace Probance\M2connector\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    private $tables = [
        'probance_mapping_coupon',
    ];

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        foreach ($this->tables as $table) {
            if (!$setup->getConnection()->isTableExists($setup->getTable($table))) {
                $table = $setup->getConnection()
                    ->newTable($setup->getTable($table))
                    ->addColumn(
                        'row_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'nullable' => false, 'primary' => true],
                        'ID'
                    )
                    ->addColumn(
                        'magento_attribute',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => false],
                        'Magento Attribute'
                    )
                    ->addColumn(
                        'probance_attribute',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => false],
                        'Probance Attribute'
                    )
                    ->addColumn(
                        'user_value',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => true],
                        'Custom Value'
                    )
                    ->addColumn(
                        'field_limit',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => true],
                        'Field Limit'
                    )
                    ->addColumn(
                        'field_type',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => false],
                        'Field Type'
                    )
                    ->addColumn(
                        'position',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false],
                        'Position'
                    );
                $setup->getConnection()->createTable($table);
            }
        }

        $setup->endSetup();
    }
}
