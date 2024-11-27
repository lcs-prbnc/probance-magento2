<?php

namespace Probance\M2connector\Cron;

use Probance\M2connector\Model\Config\Source\ExportType;
use Probance\M2connector\Model\Config\Source\Sync\Mode;
use Probance\M2connector\Helper\Data as ProbanceHelper;

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
    protected $probanceHelper;

    /**
     * @var Array
     */
    protected $exportList;

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
        foreach ($this->probanceHelper->getStoresList() as $store) {
            $this->probanceHelper->setFlowStore($store->getId());
        
            if ($this->probanceHelper->getGivenFlowValue($this->flow, 'sync_mode') != Mode::SYNC_MODE_SCHEDULED_TASK) {
                return;
            }

            $range = false;
            $export_type = $this->probanceHelper->getGivenFlowValue($this->flow, 'export_type');
            if (is_null($export_type) || ($export_type == ExportType::EXPORT_TYPE_UPDATED)) {
                $range = $this->probanceHelper->getExportRangeDate($this->flow);
            }
        
            foreach ($this->exportList as $id => $exportJob) 
            {
                if ($range) $exportJob->setRange($range['from'], $range['to']);
                if ($id > 0) $exportJob->setIsSameseq(true);
                $exportJob->export($store->getId());
            }
        }

        return;
    }
}
