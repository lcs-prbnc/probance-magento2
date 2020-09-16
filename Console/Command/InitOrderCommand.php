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

class InitOrderCommand extends Command
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
     * InitOrderCommand constructor.
     *
     * @param State $state
     * @param Order $order
     */
    public function __construct(
        State $state,
        ProgressBar $progressBar,
        Order $order
    )
    {
        $this->state = $state;
        $this->progressBar = $progressBar;
        $this->order = $order;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('probance:init:order');
        $this->setDescription('Init orders to probance');

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
            $output->writeln($this->progressBar->getLogo());
            $output->writeln('<info>Preparing to export orders...</info>');

            $this->order
                ->setProgressBar($this->progressBar->getProgressBar($output))
                ->export();

            $output->writeln("");
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}