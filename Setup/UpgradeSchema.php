<?php

namespace Walkwizus\Probance\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $logTable = 'probance_log';

        if (!$setup->getConnection()->isTableExists($setup->getTable($logTable))) {
            $table = $setup->getConnection()
                ->newTable($logTable)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'filename',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Filename'
                )
                ->addColumn(
                    'errors',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Errors'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Created At'
                );
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}