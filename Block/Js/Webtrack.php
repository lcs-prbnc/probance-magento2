<?php

namespace Probance\M2connector\Block\Js;

use Magento\Framework\View\Element\Template;
use Probance\M2connector\Helper\Data;

class Webtrack extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Webtrack constructor.
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
        if (!($this->helper->getWebtrackingValue('web_enabled')
              || $this->helper->getWebtrackingValue('cart_enabled'))
            || empty($this->getScriptUrl()))    {
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
}
