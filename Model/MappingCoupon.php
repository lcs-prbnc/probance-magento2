<?php

namespace Walkwizus\Probance\Model;

use Magento\Framework\Model\AbstractModel;

class MappingCoupon extends AbstractModel
{
    const CACHE_TAG = 'walkwizus_probance_mapping_coupon';

    protected $_cacheTag = 'walkwizus_probance_mapping_coupon';

    protected $_eventPrefix = 'walkwizus_probance_mapping_coupon';

    protected function _construct()
    {
        $this->_init(\Walkwizus\Probance\Model\ResourceModel\MappingCoupon::class);
    }
}
