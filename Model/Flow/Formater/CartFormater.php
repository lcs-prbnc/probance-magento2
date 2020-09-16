<?php

namespace Walkwizus\Probance\Model\Flow\Formater;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;

class CartFormater extends AbstractFormater
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var array
     */
    private $relations = [];

    /**
     * CartFormater constructor.
     *
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(QuoteRepository $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param array $relations
     */
    public function setProductRelation(array $relations)
    {
        $this->relations = $relations;
    }

    /**
     * Get child product ID
     *
     * @param $item
     * @return mixed|string
     */
    public function getChildId($item)
    {
        if (!isset($this->relations[$item->getProductId()])) {
            return '';
        }

        return $this->relations[$item->getProductId()];
    }

    /**
     * Get Customer ID
     *
     * @param $item
     * @return int
     * @throws NoSuchEntityException
     */
    public function getCustomerId($item)
    {
        return $this->quoteRepository->get($item->getQuoteId())->getCustomerId();
    }

    /**
     * Get product ID
     *
     * @param $item
     * @return mixed
     */
    public function getProductId($item)
    {
        return $item->getProductId();
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getRowTotalInclTax($item)
    {
        return $item->getRowTotalInclTax();
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getCreatedAt($item)
    {
        return $item->getCreatedAt();
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getUpdatedAt($item)
    {
        return $item->getUpdatedAt();
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getIsQtyDecimal($item)
    {
        return $item->getIsQtyDecimal();
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getBasePrice($item)
    {
        return $item->getBasePrice();
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getPrice($item)
    {
        return $item->getPrice();
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getBaseOriginalPrice($item)
    {
        return $item->getBaseOriginalPrice();
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getOriginalPrice($item)
    {
        return $item->getOriginalPrice();
    }
}