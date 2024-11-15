<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\Config\Scope;
use Magento\Framework\App\State;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Order\Proxy as Order;

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
     * @param Scope $scope
     * @param State $state
     * @param ProgressBar $progressBar
     * @param ProbanceHelper $probanceHelper
     * @param Order $order
     */
    public function __construct(
        Scope $scope,
        State $state,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        Order $order
    )
    {
        parent::__construct($scope, $state, $progressBar, $probanceHelper);
        if ($probanceHelper->getGivenFlowValue('order', 'enabled')) {
            $this->exportList[] = array(
                'title' => 'Preparing to export orders...',
                'job'   => $order
            );
        }
    }
}
