<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Model\AbstractModel;

class MappingCart extends AbstractModel
{
    const CACHE_TAG = 'Probance_M2connector_mapping_cart';

    protected $_cacheTag = 'Probance_M2connector_mapping_cart';

    protected $_eventPrefix = 'Probance_M2connector_mapping_cart';

    protected function _construct()
    {
        $this->_init(\Probance\M2connector\Model\ResourceModel\MappingCart::class);
    }
}