<?php

namespace Walkwizus\Probance\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MappingArticle extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('probance_mapping_article', 'row_id');
    }

    public function deleteArticleMapping()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['row_id > ?' => 0]
        );
    }
}