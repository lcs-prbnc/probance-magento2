<?php

namespace Probance\M2connector\Model\ResourceModel\MappingProductLang;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'row_id';

    public function _construct()
    {
        $this->_init('Probance\M2connector\Model\MappingProductLang', 'Probance\M2connector\Model\ResourceModel\MappingProductLang');
    }
}
