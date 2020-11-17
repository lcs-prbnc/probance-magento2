<?php

namespace Walkwizus\Probance\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Walkwizus\Probance\Helper\ProgressBar;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\Cart;

class InitCartCommand extends ExportCartCommand
{
    /**
     * @var Boolean
     */
    protected $can_use_range = false;

    /**
     * @var string
     */
    protected $command_line = 'probance:init:cart';
}
