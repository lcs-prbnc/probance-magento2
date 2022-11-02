<?php
namespace Probance\M2connector\Model\Config\Source;

use Magento\Store\Model\System\Store as MagentoStore;

class Store extends MagentoStore
{
    public function toOptionArray()
    {
        return $this->getStoreValuesForForm(true);
    }
}
