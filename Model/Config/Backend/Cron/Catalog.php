<?php

namespace Walkwizus\Probance\Model\Config\Backend\Cron;

class Catalog extends Base
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/probance_export_catalog/schedule/cron_expr';

    /**
     * Cron model path
     */
    const CRON_MODEL_PATH = 'crontab/default/jobs/probance_export_catalog/run/model';

    /**
     * System config time path
     */
    const SYSTEM_CONFIG_TIME_PATH = 'groups/catalog_flow/fields/time/value';
    /**
     * System config frequency path
     */
    const SYSTEM_CONFIG_FREQUENCY_PATH = 'groups/catalog_flow/fields/frequency/value';

}
