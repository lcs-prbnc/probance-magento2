<?php

namespace Walkwizus\Probance\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    public function _construct()
    {
        $this->_init('Walkwizus\Probance\Model\Log', 'Walkwizus\Probance\Model\ResourceModel\Log');
    }
}