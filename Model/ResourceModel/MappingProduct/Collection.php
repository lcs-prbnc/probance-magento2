<?php

namespace Walkwizus\Probance\Model\ResourceModel\MappingProduct;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'row_id';

    public function _construct()
    {
        $this->_init('Walkwizus\Probance\Model\MappingProduct', 'Walkwizus\Probance\Model\ResourceModel\MappingProduct');
    }
}