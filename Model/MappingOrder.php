<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Model\AbstractModel;

class MappingOrder extends AbstractModel
{
    const CACHE_TAG = 'Probance_M2connector_mapping_order';

    protected $_cacheTag = 'Probance_M2connector_mapping_order';

    protected $_eventPrefix = 'Probance_M2connector_mapping_order';

    protected function _construct()
    {
        $this->_init(\Probance\M2connector\Model\ResourceModel\MappingOrder::class);
    }
}