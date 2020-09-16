<?php

namespace Walkwizus\Probance\Model\Flow\Renderer;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\CustomAttributesDataInterface;

interface RendererInterface
{
    /**
     * @param CustomAttributesDataInterface $entity
     * @param AbstractAttribute $attribute
     * @return mixed
     */
    public function render(
        CustomAttributesDataInterface $entity,
        AbstractAttribute $attribute
    );
}