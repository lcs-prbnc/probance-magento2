<?php

namespace Walkwizus\Probance\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Log extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('probance_log', 'entity_id');
    }
}