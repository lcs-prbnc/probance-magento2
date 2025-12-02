<?php

namespace Probance\M2connector\Model\Flow\Renderer\Factory;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Probance\M2connector\Model\Flow\Renderer\RendererInterface;

class MultiselectRenderer implements RendererInterface
{
    /**
     * @param CustomAttributesDataInterface $entity
     * @param AbstractAttribute $attribute
     * @return mixed|string
     */
    public function render(CustomAttributesDataInterface $entity, AbstractAttribute $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        $optionIds = $entity->getData($attributeCode);
        if ($optionIds) {
            $values = [];
            $optionIds = array_map('trim', explode(',', $optionIds));
            $entityAttribute = $entity->getResource()->getAttribute($attributeCode);
            if ($entityAttribute) {
                $entityAttributeSource = $entityAttribute->getSource();
                if ($entityAttributeSource) {
                    foreach ($optionIds as $optionId) {
                        $values[] = $entityAttributeSource->getOptionText($optionId);
                    }
                }
            }
            return implode(',', $values);
        }
        return '';
    }
}
