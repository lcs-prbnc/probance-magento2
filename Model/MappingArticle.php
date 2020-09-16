<?php

namespace Walkwizus\Probance\Model;

use Magento\Framework\Model\AbstractModel;

class MappingArticle extends AbstractModel
{
    const CACHE_TAG = 'walkwizus_probance_mapping_article';

    protected $_cacheTag = 'walkwizus_probance_mapping_article';

    protected $_eventPrefix = 'walkwizus_probance_mapping_article';

    protected function _construct()
    {
        $this->_init(\Walkwizus\Probance\Model\ResourceModel\MappingArticle::class);
    }
}