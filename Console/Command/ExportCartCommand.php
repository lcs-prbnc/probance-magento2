<?php

namespace Walkwizus\Probance\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Walkwizus\Probance\Helper\ProgressBar;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\Cart;

class ExportCartCommand extends AbstractFlowExportCommand
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'cart';

    /**
     * @var string
     */
    protected $command_line = 'probance:export:cart';

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
        Cart $cart
    )
    {
        parent::__construct($state, $progressBar, $probanceHelper);
        $this->exportList[] = array(
            'title' => 'Preparing to export carts...',
            'job'   => $cart
        );
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName($this->command_line);
        $this->setDescription('Export carts to probance');

        parent::configure();
    }
}
