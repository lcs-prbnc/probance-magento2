<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Order\Proxy as Order;
use Psr\Log\LoggerInterface;

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
     * @var string
     */
    protected $command_desc = 'Export orders to probance';

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
        LoggerInterface $logger,
        Order $order
    )
    {
        parent::__construct($state, $progressBar, $probanceHelper, $logger);
        $this->exportList[] = array(
            'title' => 'Preparing to export orders...',
            'job'   => $order
        );
    }
}
