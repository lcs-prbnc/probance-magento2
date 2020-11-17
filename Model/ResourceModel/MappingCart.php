<?php

namespace Probance\M2connector\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MappingCart extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('probance_mapping_cart', 'row_id');
    }

    public function deleteCartMapping()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['row_id > ?' => 0]
        );
    }
}