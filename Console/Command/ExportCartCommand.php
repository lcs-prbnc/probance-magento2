<?php

namespace Walkwizus\Probance\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Walkwizus\Probance\Helper\ProgressBar;
use Walkwizus\Probance\Model\Export\Cart;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;

class ExportCartCommand extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var ProbanceHelper
     */
    private $probanceHelper;

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
        Cart $cart,
        ProbanceHelper $probanceHelper
    )
    {
        $this->state = $state;
        $this->progressBar = $progressBar;
        $this->cart = $cart;
        $this->probanceHelper = $probanceHelper;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('probance:export:cart');
        $this->setDescription('Export carts to probance');

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
            $output->writeln('<info>Preparing to export carts...</info>');

            $this->cart
                ->setProgressBar($this->progressBar->getProgressBar($output))
                ->setRange($range['from'], $range['to'])
                ->export();

            $output->writeln("");
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}