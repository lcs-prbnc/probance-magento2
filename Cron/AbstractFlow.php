<?php

namespace Walkwizus\Probance\Cron;

use Walkwizus\Probance\Model\Config\Source\ExportType;
use Walkwizus\Probance\Model\Config\Source\Sync\Mode;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;

abstract class AbstractFlow
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = '';

    /**
     * @var ProbanceHelper
     */
    private $probanceHelper;

    /**
     * @var Array
     */
    private $exportList;

    /**
     * Catalog constructor.
     *
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        ProbanceHelper $probanceHelper
    )
    {
        $this->probanceHelper = $probanceHelper;
    }

    /**
     * Export catalog product
     */
    public function execute()
    {
        if ($this->probanceHelper->getGivenFlowValue($this->flow, 'sync_mode') != Mode::SYNC_MODE_SCHEDULED_TASK) {
            return;
        }

        $range = false;
        $export_type = $this->probanceHelper->getGivenFlowValue($this->flow, 'export_type');
        if (is_null($export_type) || ($export_type == ExportType::EXPORT_TYPE_UPDATED)) {
            $range = $this->probanceHelper->getExportRangeDate();
        }
        
        foreach ($this->exportList as $export) 
        {
            if ($range) $export->setRange($range['from'], $range['to']);
            $export->export();
        }

        return;
    }
}
