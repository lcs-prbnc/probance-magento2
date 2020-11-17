<?php

namespace Probance\M2connector\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ExportType implements OptionSourceInterface
{
    const EXPORT_TYPE_ALL = 1;

    const EXPORT_TYPE_UPDATED = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'All products',
                'value' => self::EXPORT_TYPE_ALL,
            ],
            [
                'label' => 'Only updated products',
                'value' => self::EXPORT_TYPE_UPDATED,
            ]
        ];
    }
}