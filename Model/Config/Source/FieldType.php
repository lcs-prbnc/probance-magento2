<?php

namespace Probance\M2connector\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FieldType implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Price',
                'value' => 'price',
            ],
            [
                'label' => 'Text',
                'value' => 'text',
            ],
            [
                'label' => 'Date',
                'value' => 'date',
            ],
            [
                'label' => 'DateTime',
                'value' => 'datetime',
            ],
        ];
    }
}