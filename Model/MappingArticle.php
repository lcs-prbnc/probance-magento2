<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Model\AbstractModel;

class MappingArticle extends AbstractModel
{
    const CACHE_TAG = 'Probance_M2connector_mapping_article';

    protected $_cacheTag = 'Probance_M2connector_mapping_article';

    protected $_eventPrefix = 'Probance_M2connector_mapping_article';

    protected function _construct()
    {
        $this->_init(\Probance\M2connector\Model\ResourceModel\MappingArticle::class);
    }
}