<?php

namespace Probance\M2connector\Model\ResourceModel;

class MappingArticleTierPrice extends AbstractMapping
{
    protected function _construct()
    {
        $this->_init('probance_mapping_article_tier_price', 'row_id');
    }
}
