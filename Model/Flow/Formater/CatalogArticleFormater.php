<?php

namespace Walkwizus\Probance\Model\Flow\Formater;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

class CatalogArticleFormater extends CatalogProductFormater
{
    private $configurable;

    public function __construct(Configurable $configurable)
    {
        $this->configurable = $configurable;
    }
}