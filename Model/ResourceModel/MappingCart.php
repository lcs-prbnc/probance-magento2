<?php

namespace Probance\M2connector\Model\ResourceModel;

class MappingCart extends AbstractMapping
{
    protected function _construct()
    {
        $this->_init('probance_mapping_cart', 'row_id');
    }
}
