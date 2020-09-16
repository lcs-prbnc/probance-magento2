<?php

namespace Walkwizus\Probance\Cron;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Config\Source\Sync\Mode;
use Walkwizus\Probance\Model\Export\Customer as CustomerExport;

class Customer
{
    /**
     * @var ProbanceHelper
     */
    private $probanceHelper;

    /**
     * @var CustomerExport
     */
    private $customerExport;

    /**
     * Customer constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param CustomerExport $customerExport
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        CustomerExport $customerExport
    )
    {
        $this->probanceHelper = $probanceHelper;
        $this->customerExport = $customerExport;
    }

    /**
     * Execute cron
     *
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function execute()
    {
        if ($this->probanceHelper->getCustomerFlowValue('sync_mode') != Mode::SYNC_MODE_SCHEDULED_TASK) {
            return;
        }

        $range = $this->probanceHelper->getExportRangeDate();

        $this->customerExport
            ->setRange($range['from'], $range['to'])
            ->export();

        return;
    }
}