<?php

namespace Probance\M2connector\Cron;

use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\ResourceModel\Log\Collection;

class Log
{
    /**
     * @var ProbanceHelper
     */
    protected $probanceHelper;

    /**
     * @var Collection
     */
    protected $logCollection;

    /**
     * Log constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param Collection $logCollection
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        Collection $logCollection
    )
    {
        $this->probanceHelper = $probanceHelper;
        $this->logCollection = $logCollection;
    }

    /**
     * Export catalog product
     */
    public function execute()
    {
        $nbDay = $this->probanceHelper->getLogRetentionValue();
        if (empty($nbday)) {
            return;
        }
        $this->doRotate($nbDay);
        return;
    }    
        
    public function doRotate($nbDay)
    {
        $date = $this->probanceHelper->getDatetime();
        $date = $date->sub(new \DateInterval('P'.$nbDay.'D'));
        $this->probanceHelper->addLog('Deleting logs older than '.$date->format('Y-m-d H:i:s'));

        $logs = $this->logCollection->filterOverRetention($date);
        foreach ($logs as $log) {
            $log->delete();
        }
    }
}
