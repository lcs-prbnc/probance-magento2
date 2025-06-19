<?php

namespace Probance\M2connector\Cron;

use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Coupon\Proxy as Coupon;

use Magento\Framework\Shell;
use Symfony\Component\Process\PhpExecutableFinder;

class ExportCouponCron extends AbstractFlowExportCron
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'coupon';

    /**
     * ExportCouponCron constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param Shell $shell
     * @param PhpExecutableFinder $phpExecutableFinder
     * @param Coupon $coupon
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        Shell $shell,
        PhpExecutableFinder $phpExecutableFinder,
        Coupon $coupon
    )
    {
        parent::__construct($probanceHelper, $shell, $phpExecutableFinder);
        $this->exportList[] = array(
            'title' => __('Preparing to export coupons...'),
            'job'   => $coupon
        );
    }
}
