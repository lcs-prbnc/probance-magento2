<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Config\Source\ExportType;
use Psr\Log\LoggerInterface;

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
    protected $exportList; 

    /**
     * @var Boolean
     */
    protected $can_use_range = true;

    /**
     * @var Boolean
     */
    protected $is_init = false;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ExportCartCommand constructor.
     *
     * @param State $state
     * @param ProgressBar $progressBar
     * @param Cart $cart
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        State $state,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        LoggerInterface $logger
    )
    {
        $this->state = $state;
        $this->progressBar = $progressBar;
        $this->probanceHelper = $probanceHelper;
        $this->logger = $logger;

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
        parent::configure();
    }

    /**
     * Execute order export
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_CRONTAB);

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
                $output->writeln('<comment>Range date forced : '.$range['from']->format('Y-m-d H:i:s').' -> '.$range['to']->format('Y-m-d H:i:s').'</comment>'); 
            }
        }

        foreach ($this->exportList as $exportJob)
        {
            try 
            {
                if (isset($exportJob['title'])) $output->writeln('<info>'.$exportJob['title'].'</info>');
                if (isset($exportJob['job'])) {
                    $exportJob['job']->setProgressBar($this->progressBar->getProgressBar($output)); 
                    if ($range) $exportJob['job']->setRange($range['from'], $range['to']);
                    $exportJob['job']->setIsInit($this->is_init);
                    $exportJob['job']->export();
                }
                $output->writeln("");
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $output->writeln("");
                $output->writeln('<error>' . $e->getMessage() . '</error>');
                $output->writeln("");
            }
        }
	return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
