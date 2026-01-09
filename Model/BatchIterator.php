<?php

namespace Probance\M2connector\Model;

use Magento\Framework\DataObject;

/**
 * Batched Iterator
 */
class BatchIterator extends DataObject
{
    /**
     * @param $collection Varien_Data_Collection
     * @param array $callback
     */
    public function walk($collection, array $callbackForIndividual, ?array $callbackAfterBatch = null, $batchSize = null)
    {
        $collection->load();
        foreach ($collection as $item) {
            call_user_func($callbackForIndividual, $item);
        }

        if ($callbackAfterBatch) call_user_func($callbackAfterBatch);

        $collection->clear();
    }
} 
