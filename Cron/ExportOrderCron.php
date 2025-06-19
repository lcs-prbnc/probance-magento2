<?php

namespace Probance\M2connector\Cron;

use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Order\Proxy as Order;

use Magento\Framework\Shell;
use Symfony\Component\Process\PhpExecutableFinder;

class ExportOrderCron extends AbstractFlowExportCron
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'order';

    /**
     * ExportOrderCron constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param Shell $shell
     * @param PhpExecutableFinder $phpExecutableFinder
     * @param Order $order
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        Shell $shell,
        PhpExecutableFinder $phpExecutableFinder,
        Order $order
    )
    {
        parent::__construct($probanceHelper, $shell, $phpExecutableFinder);
        $this->exportList[] = array(
            'title' => __('Preparing to export orders...'),
            'job'   => $order
        );
    }
}
