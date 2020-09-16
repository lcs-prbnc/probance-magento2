<?php

namespace Walkwizus\Probance\Cron;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Config\Source\Sync\Mode;
use Walkwizus\Probance\Model\Export\Order as OrderExport;

class Order
{
    /**
     * @var ProbanceHelper
     */
    private $probanceHelper;

    /**
     * @var OrderExport
     */
    private $orderExport;

    /**
     * Order constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param OrderExport $orderExport
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        OrderExport $orderExport
    )
    {
        $this->probanceHelper = $probanceHelper;
        $this->orderExport = $orderExport;
    }

    /**
     * Execute cron
     *
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function execute()
    {
        if ($this->probanceHelper->getOrderFlowValue('sync_mode') != Mode::SYNC_MODE_SCHEDULED_TASK) {
            return;
        }

        $range = $this->probanceHelper->getExportRangeDate();

        $this->orderExport
            ->setRange($range['from'], $range['to'])
            ->export();

        return;
    }
}