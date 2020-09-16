<?php

namespace Walkwizus\Probance\Model;

use Magento\Framework\Model\AbstractModel;

class MappingCart extends AbstractModel
{
    const CACHE_TAG = 'walkwizus_probance_mapping_cart';

    protected $_cacheTag = 'walkwizus_probance_mapping_cart';

    protected $_eventPrefix = 'walkwizus_probance_mapping_cart';

    protected function _construct()
    {
        $this->_init(\Walkwizus\Probance\Model\ResourceModel\MappingCart::class);
    }
}