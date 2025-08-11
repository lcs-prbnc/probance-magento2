<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\PhpExecutableFinder;
use Magento\Framework\Config\Scope;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DirectoryList;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Cart\Proxy as Cart;
use Probance\M2connector\Model\Shell;

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
     * @param DirectoryList $dir
     * @param ProgressBar $progressBar
     * @param ProbanceHelper $probanceHelper
     * @param Shell $shell
     * @param PhpExecutableFinder $phpExecutableFinder
     * @param Cart $cart
     */
    public function __construct(
        Scope $scope,
        State $state,
        DirectoryList $dir,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        Shell $shell,
        PhpExecutableFinder $phpExecutableFinder,
        Cart $cart
    )
    {
        parent::__construct($scope, $state, $dir, $progressBar, $probanceHelper, $shell, $phpExecutableFinder);
        $this->exportList[] = array(
            'title' => __('Preparing to export carts...'),
            'job'   => $cart
        );
    }
}
