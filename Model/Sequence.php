<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Model\AbstractModel;

class Sequence extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Sequence::class);
    }
}