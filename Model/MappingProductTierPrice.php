<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Model\AbstractModel;

class MappingProductTierPrice extends AbstractModel
{
    const CACHE_TAG = 'Probance_M2connector_mapping_product_tier_price';

    protected $_cacheTag = 'Probance_M2connector_mapping_product_tier_price';

    protected $_eventPrefix = 'Probance_M2connector_mapping_product_tier_price';

    protected function _construct()
    {
        $this->_init(ResourceModel\MappingProductTierPrice::class);
    }
}
