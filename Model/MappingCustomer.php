<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Model\AbstractModel;

class MappingCustomer extends AbstractModel
{
    const CACHE_TAG = 'Probance_M2connector_mapping_customer';

    protected $_cacheTag = 'Probance_M2connector_mapping_customer';

    protected $_eventPrefix = 'Probance_M2connector_mapping_customer';

    protected function _construct()
    {
        $this->_init(\Probance\M2connector\Model\ResourceModel\MappingCustomer::class);
    }
}