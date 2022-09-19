<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Coupon;
use Psr\Log\LoggerInterface;

class ExportCouponCommand extends AbstractFlowExportCommand
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'coupon';

    /**
     * @var string
     */
    protected $command_line = 'probance:export:coupon';

    /**
     * @var string
     */
    protected $command_desc = 'Export coupons to probance';

    /**
     * ExportCouponCommand constructor.
     *
     * @param State $state
     * @param ProgressBar $progressBar
     * @param Coupon $coupon
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        State $state,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        LoggerInterface $logger,
        Coupon $coupon
    )
    {
        parent::__construct($state, $progressBar, $probanceHelper,$logger);
        $this->exportList[] = array(
            'title' => 'Preparing to export coupons...',
            'job'   => $coupon
        );
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName($this->command_line);
        $this->setDescription('Export coupons to probance');

        parent::configure();
    }
}
