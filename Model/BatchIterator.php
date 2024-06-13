<?php

namespace Probance\M2connector\Model;

use Magento\Framework\DataObject;

/**
 * Batched Iterator
 */
class BatchIterator extends DataObject
{
    const DEFAULT_BATCH_SIZE = 500;

    private $progressBar = null;

    public function setProgressBar($progressBar) 
    {
        $this->progressBar = $progressBar;
    }

    /**
     * @param $collection Varien_Data_Collection
     * @param array $callback
     */
    public function walk($collection, array $callbackForIndividual, array $callbackAfterBatch = null, $batchSize = null)
    {
        if (!$batchSize) {
            $batchSize = self::DEFAULT_BATCH_SIZE;
        }

        $collection->setPageSize($batchSize);

        $currentPage = 1;
        $pages = $collection->getLastPageNumber();

        do {
            $collection->setCurPage($currentPage);
            if ($this->progressBar) $this->progressBar->setMessage('Selecting : '.$collection->getSelect(), 'warn');
            $collection->load();
            foreach ($collection as $item) {
                call_user_func($callbackForIndividual, $item);
            }

            if ($callbackAfterBatch) call_user_func($callbackAfterBatch);

            $currentPage++;
            $collection->clear();
        } while ($currentPage <= $pages);
    }
} 
