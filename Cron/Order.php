<?php

namespace Walkwizus\Probance\Cron;

use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\Order as OrderExport;

class Order extends AbstractFlow
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'order';

    /**
     * Coupon constructor.
     *
     * @param CouponExport $couponExport
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        OrderExport $orderExport
    )
    {
        parent::__construct($probanceHelper);
        $this->exportList[] = $orderExport;
    }
}
