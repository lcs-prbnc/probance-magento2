<?php

namespace Walkwizus\Probance\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Sequence extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('probance_sequence', 'entity_id');
    }
}