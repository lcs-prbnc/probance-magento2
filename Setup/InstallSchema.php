<?php

namespace Probance\M2connector\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    private $tables = [
        'probance_mapping_customer',
        'probance_mapping_product',
        'probance_mapping_article',
        'probance_mapping_order',
        'probance_mapping_cart',
    ];

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
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

        $sequenceTable = 'probance_sequence';

        if (!$setup->getConnection()->isTableExists($setup->getTable($sequenceTable))) {
            $table = $setup->getConnection()
                ->newTable($sequenceTable)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'flow',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Flow Name'
                )
                ->addColumn(
                    'value',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Value'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => false],
                    'Created At'
                );
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}