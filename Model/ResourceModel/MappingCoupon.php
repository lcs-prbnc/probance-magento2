<?php

namespace Probance\M2connector\Model\ResourceModel;

class MappingCoupon extends AbstractMapping
{
    protected function _construct()
    {
        $this->_init('probance_mapping_coupon', 'row_id');
    }
}
