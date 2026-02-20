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
        $customAttribute = $entity->getCustomAttribute($attributeCode);
        $data = null;
        if ($customAttribute) $data = $customAttribute->getValue();
        if ($data != null) {
            $values = [];
            $optionIds = array_map('trim', explode(',', $data));
            $entityAttributeSource = $attribute->getSource();
            if ($entityAttributeSource) {
                foreach ($optionIds as $optionId) {
                    $values[] = $entityAttributeSource->getOptionText($optionId);
                }
            }
            return implode(',', $values);
        }
        return '';
    }
}
