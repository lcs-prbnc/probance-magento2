<?php

namespace Probance\M2connector\Block\Js;

use Magento\Customer\Model\Session\Proxy as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Probance\M2connector\Helper\Data;


class Visit extends Template
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
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * Visit constructor.
     *
     * @param Data $helper
     * @param CustomerSession $customerSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        CustomerSession $customerSession,
        CatalogHelper $catalogHelper,
        Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->catalogHelper = $catalogHelper;        
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->helper->getWebtrackingValue('web_enabled')) {
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
        $cacheKeyInfo['product_id'] = $this->getProductId();
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

    public function getProductId()
    {
        $product = $this->catalogHelper->getProduct();
        return ($product ? $product->getId() : '');
    }
}
