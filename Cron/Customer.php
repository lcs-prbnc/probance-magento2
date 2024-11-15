<?php

namespace Probance\M2connector\Cron;

use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Customer as CustomerExport;

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
        if ($probanceHelper->getGivenFlowValue('customer', 'enabled')) {
            $this->exportList[] = $customerExport;
        }
    }
}
