<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Model\AbstractModel;

class MappingProductLang extends AbstractModel
{
    const CACHE_TAG = 'Probance_M2connector_mapping_product_lang';

    protected $_cacheTag = 'Probance_M2connector_mapping_product_lang';

    protected $_eventPrefix = 'Probance_M2connector_mapping_product_lang';

    protected function _construct()
    {
        $this->_init(ResourceModel\MappingProductLang::class);
    }
}
