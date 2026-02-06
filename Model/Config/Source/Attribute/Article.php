<?php

namespace Probance\M2connector\Model\Config\Source\Attribute;

class Article extends Product
{
    const CACHE_NAME = 'Article';

    private $additionnalAttributes = [
        [
            'label' => 'Parent ID (Configurable Product ID)',
            'value' => 'parent_id',
        ],
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionsMerged = $this->loadAttributeArray();
        if (!$optionsMerged) {
        
            $optionsMerged = array_merge(parent::toOptionArray(), $this->getAdditionnalAttributes());

            usort($optionsMerged, function($a, $b) {
                return $a['label'] <=> $b['label'];
            });
    
            // Use cache
            $this->saveAttributeArray($optionsMerged);
        }

        return $optionsMerged;
    }

    public function getAdditionnalAttributes()
    {
        return array_merge(parent::getAdditionnalAttributes(), $this->additionnalAttributes);
    }
}
