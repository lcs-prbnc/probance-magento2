<?php

namespace Walkwizus\Probance\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MappingOrder extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('probance_mapping_order', 'row_id');
    }

    public function deleteOrderMapping()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['row_id > ?' => 0]
        );
    }
}