<?php

namespace Walkwizus\Probance\Model;

use Magento\Framework\Model\AbstractModel;

class Sequence extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Sequence::class);
    }
}