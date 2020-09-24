<?php

namespace Walkwizus\Probance\Model\Config\Backend\Cron;

class Cart extends Base
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/probance_export_cart/schedule/cron_expr';

    /**
     * Cron model path
     */
    const CRON_MODEL_PATH = 'crontab/default/jobs/probance_export_cart/run/model';

    /**
     * System config time path
     */
    const SYSTEM_CONFIG_TIME_PATH = 'groups/cart_flow/fields/time/value';
    /**
     * System config frequency path
     */
    const SYSTEM_CONFIG_FREQUENCY_PATH = 'groups/cart_flow/fields/frequency/value';

}
