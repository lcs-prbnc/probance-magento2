<?php

namespace Probance\M2connector\Model\ResourceModel;

class MappingOrder extends AbstractMapping
{
    protected function _construct()
    {
        $this->_init('probance_mapping_order', 'row_id');
    }
}
