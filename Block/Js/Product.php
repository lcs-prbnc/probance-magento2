<?php

namespace Walkwizus\Probance\Block\Js;

use Magento\Catalog\Block\Product\View;

class Product extends View
{
    /**
     * @return int|string
     */
    public function _toHtml()
    {
        return $this->getProduct()->getId();
    }
}