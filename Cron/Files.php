<?php

namespace Probance\M2connector\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\AbstractFlow;

class Files
{
    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var ProbanceHelper
     */
    protected $probanceHelper;

    /**
     * Log constructor.
     *
     * @param DirectoryList $directoryList
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList
    )
    {
        $this->probanceHelper = $probanceHelper;
        $this->directoryList = $directoryList;
    }

    /**
     * Export catalog product
     */
    public function execute()
    {
        $nbDay = $this->probanceHelper->getFilesRetentionValue();
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
        $threshold = $date->getTimestamp();
        $this->probanceHelper->addLog('Deleting files older than '.$date->format('Y-m-d H:i:s'));

        $exportDir = $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . AbstractFlow::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR;
        $this->doClean($exportDir,$threshold);
    }

    public function doClean($dir,$threshold)
    {
        $files = \Magento\Framework\Filesystem\Glob::glob($dir . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                if ($threshold >= filemtime($file)) {
                    @unlink($file);
                }
            } 
            if (is_dir($file)) {
                $this->doClean($file.DIRECTORY_SEPARATOR,$threshold);
            }
        }
    }
}
