<?php

namespace Walkwizus\Probance\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Walkwizus\Probance\Helper\ProgressBar;
use Walkwizus\Probance\Model\Export\Customer;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;

class ExportCustomerCommand extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var ProbanceHelper
     */
    private $probanceHelper;

    /**
     * ExportCustomerCommand constructor.
     *
     * @param State $state
     * @param ProgressBar $progressBar
     * @param Customer $customer
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        State $state,
        ProgressBar $progressBar,
        Customer $customer,
        ProbanceHelper $probanceHelper
    )
    {
        $this->state = $state;
        $this->progressBar = $progressBar;
        $this->customer = $customer;
        $this->probanceHelper = $probanceHelper;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('probance:export:customer');
        $this->setDescription('Export customers to probance');

        parent::configure();
    }

    /**
     * Execute customer export
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
            $output->writeln('<info>Preparing to export customers...</info>');

            $this->customer
                ->setRange($range['from'], $range['to'])
                ->setProgressBar($this->progressBar->getProgressBar($output))
                ->export();

            $output->writeln("");
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}