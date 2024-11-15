<?php

namespace Probance\M2connector\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AbstractMapping extends AbstractDb
{
    protected function _construct()
    {
        // To be defined by subclasses
    }

    /**
     * @return self
    **/
    public function deleteMapping()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['row_id > ?' => 0]
        );
        return $this;
    }
}
