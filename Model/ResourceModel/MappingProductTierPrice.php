<?php

namespace Probance\M2connector\Model\ResourceModel;

class MappingProductTierPrice extends AbstractMapping
{
    protected function _construct()
    {
        $this->_init('probance_mapping_product_tier_price', 'row_id');
    }
}
