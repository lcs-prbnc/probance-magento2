<?php

namespace Walkwizus\Probance\Model;

use Magento\Framework\Model\AbstractModel;

class MappingProduct extends AbstractModel
{
    const CACHE_TAG = 'walkwizus_probance_mapping_product';

    protected $_cacheTag = 'walkwizus_probance_mapping_product';

    protected $_eventPrefix = 'walkwizus_probance_mapping_product';

    protected function _construct()
    {
        $this->_init(ResourceModel\MappingProduct::class);
    }
}