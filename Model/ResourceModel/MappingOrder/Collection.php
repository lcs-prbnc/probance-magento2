<?php

namespace Walkwizus\Probance\Model\ResourceModel\MappingOrder;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'row_id';

    public function _construct()
    {
        $this->_init('Walkwizus\Probance\Model\MappingOrder', 'Walkwizus\Probance\Model\ResourceModel\MappingOrder');
    }
}