<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Model\AbstractModel;

class MappingCoupon extends AbstractModel
{
    const CACHE_TAG = 'Probance_M2connector_mapping_coupon';

    protected $_cacheTag = 'Probance_M2connector_mapping_coupon';

    protected $_eventPrefix = 'Probance_M2connector_mapping_coupon';

    protected function _construct()
    {
        $this->_init(\Probance\M2connector\Model\ResourceModel\MappingCoupon::class);
    }
}
