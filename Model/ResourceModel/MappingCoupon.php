<?php

namespace Walkwizus\Probance\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MappingCoupon extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('probance_mapping_coupon', 'row_id');
    }

    public function deleteCouponMapping()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['row_id > ?' => 0]
        );
    }
}
