<?php

namespace Probance\M2connector\Model\Config\Source\Cron;

class Frequency extends \Magento\Cron\Model\Config\Source\Frequency
{
    const CRON_EVERY_HOUR = 'H';
    const CRON_DAILY_WITH_EVERY_HOUR = 'D_H';

    public function toOptionArray()
    {
        $frequency = [
            [
                'label' => __('Every hour'),
                'value' => self::CRON_EVERY_HOUR,
            ],
            [
                'label' => __('Daily and every hour'),
                'value' => self::CRON_DAILY_WITH_EVERY_HOUR,
            ]
        ];

        return array_merge($frequency, parent::toOptionArray());
    }
}
