<?php

namespace Probance\M2connector\Model\Flow\Formater;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

class CatalogArticleFormater extends CatalogProductFormater
{
    protected $configurable;

    public function __construct(Configurable $configurable)
    {
        $this->configurable = $configurable;
    }
}
