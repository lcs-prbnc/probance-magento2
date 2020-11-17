<?php

namespace Probance\M2connector\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MappingCustomer extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('probance_mapping_customer', 'row_id');
    }

    public function deleteCustomerMapping()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['row_id > ?' => 0]
        );
    }
}