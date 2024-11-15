<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\Config\Scope;
use Magento\Framework\App\State;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Cart\Proxy as Cart;

class ExportCartCommand extends AbstractFlowExportCommand
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'cart';

    /**
     * @var string
     */
    protected $command_line = 'probance:export:cart';

    /**
     * @var string
     */
    protected $command_desc = 'Export carts to probance';

    /**
     * ExportCartCommand constructor.
     *
     * @param Scope $scope
     * @param State $state
     * @param ProgressBar $progressBar
     * @param ProbanceHelper $probanceHelper
     * @param Cart $cart
     */
    public function __construct(
        Scope $scope,
        State $state,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        Cart $cart
    )
    {
        parent::__construct($scope, $state, $progressBar, $probanceHelper);
        if ($probanceHelper->getGivenFlowValue('cart', 'enabled')) {
            $this->exportList[] = array(
                'title' => 'Preparing to export carts...',
                'job'   => $cart
            );
        }
    }
}
