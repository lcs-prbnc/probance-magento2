<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Config\Scope;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Config\Source\ExportType;

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

    protected $minSecondsBetweenRedraws = 1;

    /**
     * ExportCartCommand constructor.
     *
     * @param State $state
     * @param ProgressBar $progressBar
     * @param Cart $cart
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        Scope $scope,
        State $state,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper
    )
    {
        $this->scope = $scope;
        $this->state = $state;
        $this->progressBar = $progressBar;
        $this->probanceHelper = $probanceHelper;

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
        parent::configure();
    }

    /**
     * Execute current flow export
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        $launchFunction = 'launch'; 
        if ($this->scope->getCurrentScope() !== Area::AREA_CRONTAB) {
            $result = $this->state->emulateAreaCode(Area::AREA_CRONTAB, array($this, $launchFunction), array($input,$output));
        } else {
            $result = $this->$launchFunction($input,$output);
        }
        $output->writeln("");
        $output->writeln('<comment>' . __('Exporting %1 is terminated.',$this->flow) . '</comment>');
        $output->writeln("");
        return $result;
    }

    /**
     * Launch exports
     * @return int|null|void
     */
    public function launch(InputInterface $input, OutputInterface $output)
    {
        $storeId = $input->getOption('store_id');
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws LocalizedException
     */
    public function launchForStore($storeId, InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Probance export on store '.$storeId.'</info>');

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
            if ($input->getOption('from') && $input->getOption('to')) {
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
            if ($input->getOption('from') || $input->getOption('to')) {
                $message = 'Range date forced, but not usable for this export';
                $output->writeln('<comment>'.$message.'</comment>');
                if ($debug) {
                    $this->probanceHelper->addLog($message, $this->flow);
                }
            }
        }

        foreach ($this->exportList as $id => $exportJob)
        {
            try 
            {
                if (isset($exportJob['title'])) $output->writeln('<info>'.$exportJob['title'].'</info>');
                if (isset($exportJob['job'])) {
                    $progressBar = $this->progressBar->getProgressBar($output);
                    if (method_exists($progressBar,'minSecondsBetweenRedraws')) $progressBar->minSecondsBetweenRedraws($this->minSecondsBetweenRedraws);
                    $exportJob['job']->setProgressBar($progressBar);
                    if ($range) $exportJob['job']->setRange($range['from'], $range['to']);
                    $exportJob['job']->setIsInit($this->is_init);
                    if ($id > 0) $exportJob->setIsSameseq(true);
                    $exportJob['job']->export($storeId);
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

    public function setMinSecondsBetweenRedraws(float $seconds)
    {
        $this->minSecondsBetweenRedraws = $seconds;
    }
}
