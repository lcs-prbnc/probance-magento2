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
                'label' => __('All'),
                'value' => self::EXPORT_TYPE_ALL,
            ],
            [
                'label' => __('Only updated ones'),
                'value' => self::EXPORT_TYPE_UPDATED,
            ]
        ];
    }
}
