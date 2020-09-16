<?php

namespace Walkwizus\Probance\Model;

use Magento\Framework\Model\AbstractModel;

class MappingCustomer extends AbstractModel
{
    const CACHE_TAG = 'walkwizus_probance_mapping_customer';

    protected $_cacheTag = 'walkwizus_probance_mapping_customer';

    protected $_eventPrefix = 'walkwizus_probance_mapping_customer';

    protected function _construct()
    {
        $this->_init(\Walkwizus\Probance\Model\ResourceModel\MappingCustomer::class);
    }
}