<?php

namespace Probance\M2connector\Model\ResourceModel;

class MappingCustomer extends AbstractMapping
{
    protected function _construct()
    {
        $this->_init('probance_mapping_customer', 'row_id');
    }
}
