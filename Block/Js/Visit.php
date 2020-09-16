<?php

namespace Walkwizus\Probance\Block\Js;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Walkwizus\Probance\Helper\Data;

class Visit extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Visit constructor.
     *
     * @param Data $helper
     * @param Session $customerSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Session $customerSession,
        Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->helper->getWebtrackingValue('enabled')) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @return mixed
     */
    public function getScriptUrl()
    {
        return $this->helper->getWebtrackingValue('url');
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->customerSession->isLoggedIn()
            ? $this->customerSession->getCustomer()->getEmail()
            : '';
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->helper->getWebtrackingValue('token');
    }
}