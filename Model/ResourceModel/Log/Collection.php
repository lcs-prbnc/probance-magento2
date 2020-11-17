<?php

namespace Probance\M2connector\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    public function _construct()
    {
        $this->_init('Probance\M2connector\Model\Log', 'Probance\M2connector\Model\ResourceModel\Log');
    }
}