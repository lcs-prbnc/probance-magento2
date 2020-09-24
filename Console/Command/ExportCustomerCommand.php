<?php

namespace Walkwizus\Probance\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\State;
use Walkwizus\Probance\Helper\ProgressBar;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Export\Customer;

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
        Customer $customer
    )
    {
        parent::__construct($state, $progressBar, $probanceHelper);
        $this->exportList[] = array(
            'title' => 'Preparing to export customers...',
            'job'   => $customer
        );
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName($this->command_line);
        $this->setDescription('Export customers to probance');

        parent::configure();
    }
}
