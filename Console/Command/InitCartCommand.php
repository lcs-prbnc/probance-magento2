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

class InitCartCommand extends Command
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
     * InitCartCommand constructor.
     *
     * @param State $state
     * @param ProgressBar $progressBar
     * @param Cart $cart
     */
    public function __construct(
        State $state,
        ProgressBar $progressBar,
        Cart $cart
    )
    {
        $this->state = $state;
        $this->progressBar = $progressBar;
        $this->cart = $cart;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('probance:init:cart');
        $this->setDescription('Init cart to probance');

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
            $output->writeln('<info>Preparing to export carts...</info>');

            $this->cart
                ->setProgressBar($this->progressBar->getProgressBar($output))
                ->export();

            $output->writeln("");
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}