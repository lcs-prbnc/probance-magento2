<?php

namespace Walkwizus\Probance\Cron;

use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\Customer as CustomerExport;

class Customer extends AbstractFlow
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'customer';

    /**
     * Coupon constructor.
     *
     * @param CouponExport $couponExport
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        CustomerExport $customerExport
    )
    {
        parent::__construct($probanceHelper);
        $this->exportList[] = $customerExport;
    }
}
