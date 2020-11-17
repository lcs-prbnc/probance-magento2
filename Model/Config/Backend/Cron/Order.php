<?php

namespace Probance\M2connector\Model\Config\Backend\Cron;

class Order extends Base
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/probance_export_order/schedule/cron_expr';

    /**
     * Cron model path
     */
    const CRON_MODEL_PATH = 'crontab/default/jobs/probance_export_order/run/model';

    /**
     * System config time path
     */
    const SYSTEM_CONFIG_TIME_PATH = 'groups/order_flow/fields/time/value';
    /**
     * System config frequency path
     */
    const SYSTEM_CONFIG_FREQUENCY_PATH = 'groups/order_flow/fields/frequency/value';

}
