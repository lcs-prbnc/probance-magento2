<?php

namespace Walkwizus\Probance\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Walkwizus\Probance\Helper\ProgressBar;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\Coupon;

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
        Coupon $coupon
    )
    {
        parent::__construct($state, $progressBar, $probanceHelper);
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
