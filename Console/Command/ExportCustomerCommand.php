<?php

namespace Probance\M2connector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Probance\M2connector\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Customer\Proxy as Customer;
use Psr\Log\LoggerInterface;

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
     * @param State $state
     * @param ProgressBar $progressBar
     * @param Customer $customer
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        State $state,
        ProgressBar $progressBar,
        ProbanceHelper $probanceHelper,
        LoggerInterface $logger,
        Customer $customer
    )
    {
        parent::__construct($state, $progressBar, $probanceHelper,$logger);
        $this->exportList[] = array(
            'title' => 'Preparing to export customers...',
            'job'   => $customer
        );
    }
}
