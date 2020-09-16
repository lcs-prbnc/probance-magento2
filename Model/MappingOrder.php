<?php

namespace Walkwizus\Probance\Model;

use Magento\Framework\Model\AbstractModel;

class MappingOrder extends AbstractModel
{
    const CACHE_TAG = 'walkwizus_probance_mapping_order';

    protected $_cacheTag = 'walkwizus_probance_mapping_order';

    protected $_eventPrefix = 'walkwizus_probance_mapping_order';

    protected function _construct()
    {
        $this->_init(\Walkwizus\Probance\Model\ResourceModel\MappingOrder::class);
    }
}