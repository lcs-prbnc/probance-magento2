<?php

namespace Probance\M2connector\Block\Js;

use Magento\Framework\View\Element\Template;
use Probance\M2connector\Helper\Data;

class Cart extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Visit constructor.
     *
     * @param Data $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
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

    /**
     * @return mixed
     */
    public function getAddtocartButtonId()
    {
        return $this->helper->getWebtrackingValue('addtocart_button_id');
    }

    /**
     * @return mixed
     */
    public function getAddtocartFormId()
    {
        return $this->helper->getWebtrackingValue('addtocart_form_id');
    }

    /**
     * @return mixed
     */
    public function getProductQuerySelector()
    {
        return $this->helper->getWebtrackingValue('product_query_selector');
    }

}
