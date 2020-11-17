<?php

namespace Walkwizus\Probance\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Walkwizus\Probance\Helper\ProgressBar;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\Order;

class ExportOrderCommand extends AbstractFlowExportCommand
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'order';

    /**
     * @var string
     */
    protected $command_line = 'probance:export:order';

    /**
     * ExportOrderCommand constructor.
     *
     * @param State $state
     * @param ProgressBar $progressBar
     * @param Order $order
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        State $state,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        Order $order
    )
    {
        parent::__construct($state, $progressBar, $probanceHelper);
        $this->exportList[] = array(
            'title' => 'Preparing to export orders...',
            'job'   => $order
        );
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName($this->command_line);
        $this->setDescription('Export orders to probance');

        parent::configure();
    }
}
