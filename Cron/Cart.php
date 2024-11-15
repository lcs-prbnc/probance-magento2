<?php

namespace Probance\M2connector\Cron;

use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Cart as CartExport;

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
        parent::__construct($probanceHelper);
        if ($probanceHelper->getGivenFlowValue('cart', 'enabled')) {
            $this->exportList[] = $cartExport;
        }
    }
}
