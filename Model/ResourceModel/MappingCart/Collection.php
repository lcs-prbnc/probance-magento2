<?php

namespace Walkwizus\Probance\Model\ResourceModel\MappingCart;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'row_id';

    public function _construct()
    {
        $this->_init('Walkwizus\Probance\Model\MappingCart', 'Walkwizus\Probance\Model\ResourceModel\MappingCart');
    }
}