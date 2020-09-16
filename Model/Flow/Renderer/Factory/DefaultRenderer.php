<?php

namespace Walkwizus\Probance\Model\Flow\Renderer\Factory;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Walkwizus\Probance\Model\Flow\Renderer\RendererInterface;

class DefaultRenderer implements RendererInterface
{
    /**
     * @param CustomAttributesDataInterface $entity
     * @param AbstractAttribute $attribute
     * @return mixed
     */
    public function render(CustomAttributesDataInterface $entity, AbstractAttribute $attribute)
    {
        return $entity->getData($attribute->getAttributeCode());
    }
}