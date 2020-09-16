<?php

namespace Walkwizus\Probance\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Walkwizus\Probance\Helper\ProgressBar;
use Walkwizus\Probance\Model\Export\Order;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;

class ExportOrderCommand extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var ProbanceHelper
     */
    private $probanceHelper;

    /**
     * InitOrderCommand constructor.
     *
     * @param State $state
     * @param ProgressBar $progressBar
     * @param Order $order
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        State $state,
        ProgressBar $progressBar,
        Order $order,
        ProbanceHelper $probanceHelper
    )
    {
        $this->state = $state;
        $this->progressBar = $progressBar;
        $this->order = $order;
        $this->probanceHelper = $probanceHelper;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('probance:export:order');
        $this->setDescription('Export orders to probance');

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

        try {
            $range = $this->probanceHelper->getExportRangeDate();

            $output->writeln($this->progressBar->getLogo());
            $output->writeln('<info>Preparing to export orders...</info>');

            $this->order
                ->setProgressBar($this->progressBar->getProgressBar($output))
                ->setRange($range['from'], $range['to'])
                ->export();

            $output->writeln("");
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}