<?php

namespace Walkwizus\Probance\Cron;

use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\Cart as CartExport;

class Cart extends AbstractFlow
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'cart';

    /**
     * Coupon constructor.
     *
     * @param CouponExport $couponExport
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        CartExport $cartExport
    )
    {
        parent::_construct($probanceHelper);
        $this->exportList[] = $cartExport;
    }
}
