<?php

namespace Walkwizus\Probance\Model\Config\Source\Filename;

use Magento\Framework\Data\OptionSourceInterface;

class Suffix implements OptionSourceInterface
{
    /**
     * Filename suffix yesterday (Ymd)
     */
    const FILENAME_SUFFIX_YESTERDAY = 1;

    /**
     * Filename suffix today (Ymd)
     */
    const FILENAME_SUFFIX_TODAY = 2;

    /**
     * Retrieve filename suffix
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Yesterday'),
                'value' => self::FILENAME_SUFFIX_YESTERDAY
            ],
            [
                'label' => __('Today'),
                'value' => self::FILENAME_SUFFIX_TODAY
            ]
        ];
    }
}