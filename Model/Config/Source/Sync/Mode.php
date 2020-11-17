<?php

namespace Probance\M2connector\Model\Config\Source\Sync;

use Magento\Framework\Data\OptionSourceInterface;

class Mode implements OptionSourceInterface
{
    /**
     * Real time mode (JSON API)
     */
    const SYNC_MODE_REAL_TIME = 1;

    /**
     * Scheduled mode (Magento cron)
     */
    const SYNC_MODE_SCHEDULED_TASK = 2;

    /**
     * Retrieve sync mode
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Real Time'),
                'value' => self::SYNC_MODE_REAL_TIME
            ],
            [
                'label' => __('Scheduled Task'),
                'value' => self::SYNC_MODE_SCHEDULED_TASK
            ]
        ];
    }
}