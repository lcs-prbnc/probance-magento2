<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Coupon;

class InitCouponCommand extends ExportCouponCommand
{
    /**
     * @var Boolean
     */
    protected $can_use_range = false;

    /**
     * @var string
     */
    protected $command_line = 'probance:init:coupon';
}
