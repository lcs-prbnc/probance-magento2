<?php

namespace Walkwizus\Probance\Model\Flow\Formater;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderFormater extends AbstractFormater
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var array
     */
    private $relations = [];

    /**
     * OrderFormater constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
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

        return $this->relations[$item->getProductId()];
    }

    /**
     * @param OrderItemInterface $item
     * @return int|null
     */
    public function getCustomerId(OrderItemInterface $item)
    {
        return $this->getOrderById($item->getOrderId())->getCustomerId();
    }

    /**
     * @param OrderItemInterface $item
     * @return int|null
     */
    public function getQuoteId(OrderItemInterface $item)
    {
        return $this->getOrderById($item->getOrderId())->getQuoteId();
    }

    /**
     * Get Order By ID
     *
     * @param $id
     * @return OrderInterface|string
     */
    private function getOrderById($id)
    {
        return $this->orderRepository->get($id);
    }

    /**
     * @param OrderItemInterface $item
     * @return string
     */
    public function getQtyOrdered(OrderItemInterface $item)
    {
        return number_format($item->getQtyOrdered());
    }
}