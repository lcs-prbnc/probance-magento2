<?php

namespace Probance\M2connector\Model\Flow\Formater;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Probance\M2connector\Helper\Data as ProbanceHelper;

class CartFormater extends AbstractFormater
{
    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var ProbanceHelper
     */
    protected $helper;

    /**
     * CustomerFormater constructor.
     *
     * @param ProbanceHelper $helper
     */
    public function __construct(
        ProbanceHelper $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @param Quote $quote
     */
    public function setQuote(Quote $quote)
    {
        $this->quote = $quote;
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
        return $this->quote->getCustomerId();
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

    /**
     * Get Customer ID
     *
     * @param $item
     * @return int
     * @throws NoSuchEntityException
     */
    public function getCustomerEmail($item)
    {
        return $this->quote->getCustomerEmail();
    }

    public function getQuoteUrl($mappingValue)
    {
        if (!empty($mappingValue)) {
            // Check starts with http(s)
            if (strpos($mappingValue,'http') !== FALSE) {
                return $mappingValue;
            } else {
                return $this->quote->getStore()->getUrl($mappingValue);
            }
        } else {
            $recoveryPath = $this->helper->getRecoveryCartPath();
            if (!empty($recoveryPath)) {
                return $this->quote->getStore()->getUrl($recoveryPath);
            } else {
                return $this->quote->getStore()->getUrl('probance/cart/recovery');
            }
        }
    }
}
