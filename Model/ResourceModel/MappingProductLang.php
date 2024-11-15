<?php

namespace Probance\M2connector\Model\ResourceModel;

class MappingProductLang extends AbstractMapping
{
    protected function _construct()
    {
        $this->_init('probance_mapping_product_lang', 'row_id');
    }
}
