<?php

namespace Probance\M2connector\Cron;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Probance\M2connector\Model\Config\Source\ExportType;
use Probance\M2connector\Model\Config\Source\Sync\Mode;
use Probance\M2connector\Helper\Data as ProbanceHelper;

use Magento\Framework\Shell;
use Symfony\Component\Process\PhpExecutableFinder;

abstract class AbstractFlowExportCron
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
     * @var Shell
     */
    private $shell;

    /**
     * @var PhpExecutableFinder
     */
    private $phpExecutableFinder;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Catalog constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param Shell $shell
     * @param PhpExecutableFinder $phpExecutableFinder
     * @param OutputInterface $output
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        Shell $shell,
        PhpExecutableFinder $phpExecutableFinder,
        OutputInterface $output
    )
    {
        $this->probanceHelper = $probanceHelper;
        $this->shell = $shell;
        $this->phpExecutableFinder = $phpExecutableFinder;
        $this->output = $output;
    }

    /**
     * Execute current flow export by cron
     */
    public function execute()
    {
        foreach ($this->probanceHelper->getStoresList() as $store) {
            $this->launchForStore($store->getId());
        }
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * Launch current flow export
     *
     * @param int $storeId
     * @throws LocalizedException
     */
    public function launchForStore($storeId)
    {
        $this->probanceHelper->setFlowStore($storeId);
        $debug = $this->probanceHelper->getDebugMode($storeId);

        if (!$this->probanceHelper->getGivenFlowValue($this->flow, 'enabled')) {
            $message = __('Your flow "%1" is disabled.',$this->flow);
            $this->probanceHelper->addLog(serialize([
                'message' => $message,
                'trace' => '',
            ]), $this->flow);
            return;
        }

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
            try 
            {
                if (isset($exportJob['job'])) {
                
                    if ($range) $exportJob['job']->setRange($range['from'], $range['to']);
                    
                    $is_sameseq = ($id > 0) ? true : false;
                    if (php_sapi_name() === 'cli') $exportJob['job']->setOutput($this->output);
                    $exportJob['job']->export($storeId,$is_sameseq);

                    // Check for next page to do
                    if ($exportJob['job']->getNextPage()) {
                        // Relaunch with jobId, nextPage and filename
                        $arguments = array(
                            'command'       => $this->getName(),
                            '--store_id'    => $storeId,
                            '--job_id'      => $id,
                            '--next_page'   => $exportJob['job']->getNextPage(),
                            '--filename'    => $exportJob['job']->getCurrentFilename()
                            );
                        if ($range) {
                            $arguments['--from'] = $range['from'];
                            $arguments['--to'] = $range['to'];
                        }

                        $aInput = new ArrayInput($arguments);

                        $phpPath = $this->phpExecutableFinder->find() ?: 'php';
                        $args = array_map(function($k, $v){
                            if ($k === 'command') return $v;
                            return "$k=$v";
                        }, array_keys($arguments), array_values($arguments));
                        $this->shell->execute($phpPath . ' ' . BP . '/bin/magento '. implode(' ',$args), [], $output);
                    }
                }
            } catch (\Exception $e) {
                $this->probanceHelper->addLog(serialize([
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]), $this->flow);
            }
        }

        return;
    }
}
