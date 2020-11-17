<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Model\AbstractModel;

class MappingProduct extends AbstractModel
{
    const CACHE_TAG = 'Probance_M2connector_mapping_product';

    protected $_cacheTag = 'Probance_M2connector_mapping_product';

    protected $_eventPrefix = 'Probance_M2connector_mapping_product';

    protected function _construct()
    {
        $this->_init(ResourceModel\MappingProduct::class);
    }
}