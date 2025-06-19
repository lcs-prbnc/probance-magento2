<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\Config\Scope;
use Magento\Framework\App\State;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Customer\Proxy as Customer;

use Probance\M2connector\Model\Shell;
use Symfony\Component\Process\PhpExecutableFinder;

class ExportCustomerCommand extends AbstractFlowExportCommand
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'customer';

    /**
     * @var string
     */
    protected $command_line = 'probance:export:customer';

    /**
     * @var string
     */
    protected $command_desc = 'Export customers to probance';

    /**
     * ExportCustomerCommand constructor.
     *
     * @param Scope $scope
     * @param State $state
     * @param ProgressBar $progressBar
     * @param ProbanceHelper $probanceHelper
     * @param Shell $shell
     * @param PhpExecutableFinder $phpExecutableFinder
     * @param Customer $customer
     */
    public function __construct(
        Scope $scope,
        State $state,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        Shell $shell,
        PhpExecutableFinder $phpExecutableFinder,
        Customer $customer
    )
    {
        parent::__construct($scope, $state, $progressBar, $probanceHelper, $shell, $phpExecutableFinder);
        $this->exportList[] = array(
            'title' => __('Preparing to export customers...'),
            'job'   => $customer
        );
    }
}
