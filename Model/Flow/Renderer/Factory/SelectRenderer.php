<?php

namespace Probance\M2connector\Model\Flow\Renderer\Factory;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Probance\M2connector\Model\Flow\Renderer\RendererInterface;

class SelectRenderer implements RendererInterface
{
    /**
     * @param CustomAttributesDataInterface $entity
     * @param AbstractAttribute $attribute
     * @return mixed|string
     */
    public function render(CustomAttributesDataInterface $entity, AbstractAttribute $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        if ($entity->getData($attributeCode)) {
            return $entity->getAttributeText($attributeCode);
        }

        return '';
    }
}
