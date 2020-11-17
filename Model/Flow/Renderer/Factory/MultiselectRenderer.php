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
        $optionIds = $entity->getData($attribute->getAttributeCode());
        if ($optionIds) {
            $values = [];
            foreach (explode(',', $optionIds) as $optionId) {
                $values[] = $entity->getResource()->getAttribute($attribute->getAttributeCode())->getSource()->getOptionText($optionId);
            }

            return implode(',', $values);
        }
        return '';
    }
}