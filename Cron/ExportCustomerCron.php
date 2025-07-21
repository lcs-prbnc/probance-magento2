<?php

namespace Probance\M2connector\Cron;

use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Export\Customer\Proxy as Customer;

use Magento\Framework\Shell;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Console\Output\ConsoleOutput;

class ExportCustomerCron extends AbstractFlowExportCron
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'customer';

    /**
     * ExportCustomerCron constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param Shell $shell
     * @param PhpExecutableFinder $phpExecutableFinder
     * @param ConsoleOutput $output
     * @param Customer $customer
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        Shell $shell,
        PhpExecutableFinder $phpExecutableFinder,
        ConsoleOutput $output,
        Customer $customer
    )
    {
        parent::__construct($probanceHelper, $shell, $phpExecutableFinder, $output);
        $this->exportList[] = array(
            'title' => __('Preparing to export customers...'),
            'job'   => $customer
        );
    }
}
