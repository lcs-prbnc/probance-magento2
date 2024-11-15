<?php

namespace Probance\M2connector\Model\ResourceModel;

class MappingProduct extends AbstractMapping
{
    protected function _construct()
    {
        $this->_init('probance_mapping_product', 'row_id');
    }
}
