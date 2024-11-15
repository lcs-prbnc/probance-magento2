<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\Config\Scope;
use Magento\Framework\App\State;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Coupon\Proxy as Coupon;

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
     * @param Scope $scope
     * @param State $state
     * @param ProgressBar $progressBar
     * @param ProbanceHelper $probanceHelper
     * @param Coupon $coupon
     */
    public function __construct(
        Scope $scope,
        State $state,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        Coupon $coupon
    )
    {
        parent::__construct($scope, $state, $progressBar, $probanceHelper);
        if ($probanceHelper->getGivenFlowValue('catalog', 'enabled')) {
            $this->exportList[] = array(
                'title' => 'Preparing to export coupons...',
                'job'   => $coupon
            );
        }
    }

}
