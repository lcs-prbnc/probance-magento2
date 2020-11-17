<?php

namespace Probance\M2connector\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MappingProduct extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('probance_mapping_product', 'row_id');
    }

    public function deleteProductMapping()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['row_id > ?' => 0]
        );
    }
}