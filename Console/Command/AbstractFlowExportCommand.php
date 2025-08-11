<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Process\PhpExecutableFinder;
use Magento\Framework\Config\Scope;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Config\Source\ExportType;
use Probance\M2connector\Model\Shell;

abstract class AbstractFlowExportCommand extends Command
{
    /**
     * @var string
     */
    protected $command_line = '';

    /**
     * @var string
     */
    protected $command_desc = '';

    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = '';    

    /**
     * @var Scope
     */
    protected $scope;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * @var ProbanceHelper
     */
    protected $probanceHelper;

    /**
     * @var Array
     */
    protected $exportList = []; 

    /**
     * @var Boolean
     */
    protected $can_use_range = true;

    /**
     * @var Boolean
     */
    protected $is_init = false;

    /**
     * @var int
     */
    protected $minSecondsBetweenRedraws = 1;

    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var PhpExecutableFinder
     */
    private $phpExecutableFinder;

    /**
     * @var DirectoryList
     */
    private $dir;

    /**
     * ExportCartCommand constructor.
     *
     * @param Scope $scope
     * @param State $state
     * @param DirectoryList $dir
     * @param ProgressBar $progressBar
     * @param Cart $cart
     * @param ProbanceHelper $probanceHelper
     * @param Shell $shell
     * @param PhpExecutableFinder $phpExecutableFinder
     */
    public function __construct(
        Scope $scope,
        State $state,
        DirectoryList $dir,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        Shell $shell,
        PhpExecutableFinder $phpExecutableFinder
    )
    {
        $this->scope = $scope;
        $this->state = $state;
        $this->progressBar = $progressBar;
        $this->probanceHelper = $probanceHelper;
        $this->shell = $shell;
        $this->phpExecutableFinder = $phpExecutableFinder;
        $this->dir = $dir;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName($this->command_line);
        $this->setDescription($this->command_desc);

        $this->addOption(
            'from',
            null,
            InputOption::VALUE_OPTIONAL,
            'Range From'
        );
        $this->addOption(
            'to',
            null,
            InputOption::VALUE_OPTIONAL,
            'Range To'
        );
        $this->addOption(
            'store_id',
            null,
            InputOption::VALUE_OPTIONAL,
            'Store id'
        );
        $this->addOption(
            'limit',
            null,
            InputOption::VALUE_OPTIONAL,
            'Limit'
        );
        $this->addOption(
            'id',
            null,
            InputOption::VALUE_OPTIONAL,
            'Entity Id'
        );
        $this->addOption(
            'job_id',
            null,
            InputOption::VALUE_OPTIONAL,
            'Id in Export List'
        );
        $this->addOption(
            'next_page',
            null,
            InputOption::VALUE_OPTIONAL,
            'Pagination in collection process'
        );
        $this->addOption(
            'filename',
            null,
            InputOption::VALUE_OPTIONAL,
            'Filename for export'
        );
        parent::configure();
    }

    public function setMinSecondsBetweenRedraws(float $seconds)
    {
        $this->minSecondsBetweenRedraws = $seconds;
    }

    /**
     * Execute current flow export
     *
     * @param \Magento\Cron\Model\Schedule|InputInterface $input - Schedule is in case of N98Magerun call
     * @param OutputInterface $output - Use OutputInterface fron constructor in case of N98Magerun call
     * @return int|null|void
     * @throws LocalizedException
     */
    protected function execute(\Magento\Cron\Model\Schedule|InputInterface $input = null, OutputInterface $output = null)
    {
        $result = \Magento\Framework\Console\Cli::RETURN_SUCCESS;

        if (is_a($input, 'Magento\Cron\Model\Schedule')) $input = null;
        if (!$output) $output = new StreamOutput(fopen($this->dir->getPath(\Magento\Framework\App\Filesystem\DirectoryList::LOG).DIRECTORY_SEPARATOR.ProbanceHelper::LOG_FILE, 'a'));

        $launchFunction = 'launch'; 
        if ($this->scope->getCurrentScope() !== Area::AREA_CRONTAB) {
            $result = $this->state->emulateAreaCode(Area::AREA_CRONTAB, array($this, $launchFunction), array($input,$output));
        } else {
            $result = $this->$launchFunction($input,$output);
        }

        return $result;
    }

    /**
     * Launch exports
     * @param \Magento\Cron\Model\Schedule|InputInterface $input - Schedule is in case of N98Magerun call
     * @param OutputInterface $output - Use OutputInterface fron constructor in case of N98Magerun call
     * @return int|null|void
     */
    public function launch(\Magento\Cron\Model\Schedule|InputInterface $input = null, OutputInterface $output = null)
    {
        $storeId = 0;
        if ($input) $storeId = $input->getOption('store_id') ? (int) $input->getOption('store_id') : 0;

        if ($storeId) {
            $this->launchForStore($storeId,$input,$output);
        } else {
            foreach ($this->probanceHelper->getStoresList() as $store) {
                $this->launchForStore($store->getId(),$input,$output);
            }
        } 
	    return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * Launch current flow export
     *
     * @param int $storeId
     * @param \Magento\Cron\Model\Schedule|InputInterface $input - Schedule is in case of N98Magerun call
     * @param OutputInterface $output - Use OutputInterface fron constructor in case of N98Magerun call
     * @throws LocalizedException
     */
    public function launchForStore($storeId, \Magento\Cron\Model\Schedule|InputInterface $input = null, OutputInterface $output = null)
    {
        // Check this first to know if command relaunch for pagination
        $nextPage = null;
        if ($input) $nextPage = $input->getOption('next_page') ? (int) $input->getOption('next_page') : null;

        if (!$nextPage) $output->writeln('<info>Probance export on store '.$storeId.'</info>');

        $this->probanceHelper->setFlowStore($storeId);
        $debug = $this->probanceHelper->getDebugMode($storeId);

        if (!$this->probanceHelper->getGivenFlowValue($this->flow, 'enabled')) {
            $message = __('Your flow "%1" is disabled.',$this->flow);
            $this->probanceHelper->addLog(serialize([
                'message' => $message,
                'trace' => '',
            ]), $this->flow);
            $output->writeln("");
            $output->writeln('<error>' . $message . '</error>');
            $output->writeln("");
            return;
        }

        $range = false;
        if ($this->can_use_range) {
            $export_type = $this->probanceHelper->getGivenFlowValue($this->flow, 'export_type');
            if (is_null($export_type) || ($export_type == ExportType::EXPORT_TYPE_UPDATED)) {
                $range = $this->probanceHelper->getExportRangeDate($this->flow);
            }
            if ($input && $input->getOption('from')) {
                if (empty($input->getOption('to'))) {
                    $now = $this->probanceHelper->getDatetime();
                    $input->setOption('to', $now->format('Y-m-d H:i:s'));
                }
                $range = [
                    'from' => new \DateTime($input->getOption('from')), 
                    'to' => new \DateTime($input->getOption('to'))
                ];
                $message = 'Range date forced : '.$range['from']->format('Y-m-d H:i:s').' -> '.$range['to']->format('Y-m-d H:i:s');
                $output->writeln('<comment>'.$message.'</comment>');
                if ($debug) {
                    $this->probanceHelper->addLog($message, $this->flow);
                }
            }
        } else {
            if ($input && 
                ($input->getOption('from') || $input->getOption('to'))
            ) {
                $message = 'Range date forced, but not usable for this export';
                $output->writeln('<comment>'.$message.'</comment>');
                if ($debug) {
                    $this->probanceHelper->addLog($message, $this->flow);
                }
            }
        }

        $limit = $entityId = $currentFilename = $jobId = null;
        if ($input) {
            $limit = $input->getOption('limit') ? (int) $input->getOption('limit') : null;
            $entityId = $input->getOption('id') ? (int) $input->getOption('id') : null;
            $currentFilename = $input->getOption('filename');
            $jobId = $input->getOption('job_id') ? (int) $input->getOption('job_id') : null;
        }

        foreach ($this->exportList as $id => $exportJob)
        {
            // Check jobId if in a relaunch
            if ($jobId && ($jobId !== $id)) continue;

            try 
            {
                if (!$nextPage && isset($exportJob['title'])) $output->writeln('<info>'.$exportJob['title'].'</info>');

                if (isset($exportJob['job'])) {
                    $exportJob['job']->setOutput($output);

                    $progressBar = $this->progressBar->getProgressBar($output);
                    $progressBar->setMessage('', 'warn');

                    if (method_exists($progressBar,'minSecondsBetweenRedraws')) $progressBar->minSecondsBetweenRedraws($this->minSecondsBetweenRedraws);
                    $exportJob['job']->setProgressBar($progressBar);
                
                    if ($range) $exportJob['job']->setRange($range['from'], $range['to']);
                    if ($limit) $exportJob['job']->setLimit($limit);
                    if ($entityId) $exportJob['job']->setEntityId($entityId);
                    if ($nextPage) {
                        $exportJob['job']->setNextPage($nextPage);
                        $progressBar->setMessage(__('Pagination needed, treating page %1',$nextPage), 'warn');
                    }
                    if ($currentFilename) $exportJob['job']->setCurrentFilename($currentFilename);

                    $exportJob['job']->setIsInit($this->is_init);

                    $is_sameseq = ($id > 0) ? true : false;
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
                            $arguments['--from'] = $range['from']->format('Y-m-d H:i:s');
                            $arguments['--to'] = $range['to']->format('Y-m-d H:i:s');
                        }

                        $verbosity = ($input && $input->hasArgument('verbosity') && $input->getArgument('verbosity') ?
                            ((intval($input->getArgument('verbosity')) >= 3) ? ['-vvv' => true] :
                                ((intval($input->getArgument('verbosity')) >= 2) ? ['-vv' => true] :
                                    ((intval($input->getArgument('verbosity')) >= 1) ? ['-v' => true] : [])
                                )
                            ) : []
                        );
                        $arguments = array_merge($arguments, $verbosity);

                        $args = array_map(function($k, $v){
                            if ($k === 'command') return $v;
                            return "$k=$v";
                        }, array_keys($arguments), array_values($arguments));
                        
                        $phpPath = $this->phpExecutableFinder->find() ?: 'php';

                        $this->shell->execute($phpPath . ' ' . BP . '/bin/magento ', $args, $output, $storeId);
                    } else {
                        // Having done last page
                        $output->writeln("");
                        $output->writeln('<comment>' . __('Exporting %1 is terminated.',$this->flow) . '</comment>');
                        $output->writeln("");
                    }
                }
                $output->writeln("");
            } catch (\Exception $e) {
                $this->probanceHelper->addLog(serialize([
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]), $this->flow);
                $output->writeln("");
                $output->writeln('<error>' . $e->getMessage() . '</error>');
                $output->writeln("");
            }
        }
    }
}
