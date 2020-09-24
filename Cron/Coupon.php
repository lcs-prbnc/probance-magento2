<?php

namespace Walkwizus\Probance\Cron;

use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\Coupon as CouponExport;

class Coupon extends AbstractFlow
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'coupon';

    /**
     * Coupon constructor.
     *
     * @param CouponExport $couponExport
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        CouponExport $couponExport
    )
    {
        parent::_construct($probanceHelper);
        $this->exportList[] = $couponExport;
    }
}
