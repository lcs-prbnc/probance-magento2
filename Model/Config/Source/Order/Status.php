<?php
namespace Probance\M2connector\Model\Config\Source\Order;

use Magento\Sales\Model\Config\Source\Order\Status as MagentoStatus;

/**
 * Order Status source model
 */
class Status extends MagentoStatus
{
    protected $_stateStatuses = null;
}
