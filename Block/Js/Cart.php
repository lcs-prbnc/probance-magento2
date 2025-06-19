<?php

namespace Probance\M2connector\Block\Js;

use Magento\Customer\Model\Session\Proxy as CustomerSession;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession; 
use Magento\Framework\View\Element\Template;
use Probance\M2connector\Helper\Data;

class Cart extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Visit constructor.
     *
     * @param Data $helper
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->helper->getWebtrackingValue('cart_enabled')) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();
        $cacheKeyInfo['customer_email'] = $this->getCustomerEmail();
        $cacheKeyInfo['quote_id'] = $this->getQuoteId();
        return $cacheKeyInfo;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->helper->getWebtrackingValue('token');
    }

    public function getCustomerEmail()
    {
        $customer = $this->customerSession->getCustomer();
        return ($customer ? $customer->getEmail() : '');
    }

    public function getQuoteId()
    {
        return $this->checkoutSession->getQuoteId();
    }

}
