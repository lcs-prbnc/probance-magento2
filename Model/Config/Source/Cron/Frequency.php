<?php

namespace Probance\M2connector\Model\Config\Source\Cron;

class Frequency extends \Magento\Cron\Model\Config\Source\Frequency
{
    const CRON_EVERY_HOUR = 'H';

    public function toOptionArray()
    {
        $frequency = [
            [
                'label' => 'Every hour',
                'value' => self::CRON_EVERY_HOUR,
            ]
        ];

        return array_merge($frequency, parent::toOptionArray());
    }
}