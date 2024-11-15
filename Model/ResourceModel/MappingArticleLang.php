<?php

namespace Probance\M2connector\Model\ResourceModel;

class MappingArticleLang extends AbstractMapping
{
    protected function _construct()
    {
        $this->_init('probance_mapping_article_lang', 'row_id');
    }
}
