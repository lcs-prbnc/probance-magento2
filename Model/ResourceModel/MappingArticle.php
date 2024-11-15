<?php

namespace Probance\M2connector\Model\ResourceModel;

class MappingArticle extends AbstractMapping
{
    protected function _construct()
    {
        $this->_init('probance_mapping_article', 'row_id');
    }
}
