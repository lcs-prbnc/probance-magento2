<?php

namespace Probance\M2connector\Model\Flow\Formater;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;

class OrderFormater extends AbstractFormater
{

    protected $orderFactory;

    /**
     * @var array
     */
    private $relations = [];

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @param OrderInterface $order
     */
    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;
    }

    /**
     * @param array $relations
     */
    public function setProductRelation(array $relations)
    {
        $this->relations = $relations;
    }

    /**
     * @param OrderItemInterface $item
     * @return string
     */
    public function getChildId(OrderItemInterface $item)
    {        
        if (!isset($this->relations[$item->getProductId()])) {
            return '';
        }
        //print_r($item->getProductId());
        //die();
       
        return $this->relations[$item->getProductId()];
    }

    /**
     * @param OrderItemInterface $item
     * @return int|null
     */
    public function getCustomerId(OrderItemInterface $item)
    {
        return $this->order->getCustomerId();
    }

    /**
     * @param OrderItemInterface $item
     * @return int|null
     */
    public function getQuoteId(OrderItemInterface $item)
    {
        return $this->order->getQuoteId();
    }

    /**
     * @param OrderItemInterface $item
     * @return string
     */
    public function getQtyOrdered(OrderItemInterface $item)
    {
        return number_format($item->getQtyOrdered());
    }

    /**
     * @param OrderItemInterface $item
     * @return string|null
     */
    public function getCustomerEmail(OrderItemInterface $item)
    {
        return $this->order->getCustomerEmail();
    }
}
