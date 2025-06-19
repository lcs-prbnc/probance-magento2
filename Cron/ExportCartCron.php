<?php

namespace Probance\M2connector\Cron;

use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Cart\Proxy as Cart;

use Magento\Framework\Shell;
use Symfony\Component\Process\PhpExecutableFinder;

class ExportCartCron extends AbstractFlowExportCron
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'cart';

    /**
     *  ExportCartCron constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param Shell $shell
     * @param PhpExecutableFinder $phpExecutableFinder
     * @param Cart $cart
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        Shell $shell,
        PhpExecutableFinder $phpExecutableFinder,
        Cart $cart
    )
    {
        parent::__construct($probanceHelper, $shell, $phpExecutableFinder);
        $this->exportList[] = array(
            'title' => __('Preparing to export carts...'),
            'job'   => $cart
        );
    }
}
