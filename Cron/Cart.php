<?php

namespace Walkwizus\Probance\Cron;

use Magento\Framework\Exception\FileSystemException;
use Walkwizus\Probance\Model\Config\Source\Sync\Mode;
use Walkwizus\Probance\Model\Export\Cart as CartExport;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;

class Cart
{
    /**
     * @var CartExport
     */
    private $cartExport;

    /**
     * @var ProbanceHelper
     */
    private $probanceHelper;

    /**
     * Cart constructor.
     *
     * @param CartExport $cartExport
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        CartExport $cartExport,
        ProbanceHelper $probanceHelper
    )
    {
        $this->cartExport = $cartExport;
        $this->probanceHelper = $probanceHelper;
    }

    /**
     * @throws FileSystemException
     */
    public function execute()
    {
        if ($this->probanceHelper->getCustomerFlowValue('sync_mode') != Mode::SYNC_MODE_SCHEDULED_TASK) {
            return;
        }

        $range = $this->probanceHelper->getExportRangeDate();

        $this->cartExport
            ->setRange($range['from'], $range['to'])
            ->export();

        return;
    }
}