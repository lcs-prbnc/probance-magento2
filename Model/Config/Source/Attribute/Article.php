<?php

namespace Probance\M2connector\Model\Config\Source\Attribute;

class Article extends Product
{
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
        $options = array_merge(parent::toOptionArray(), $this->getAdditionnalAttributes());

        usort($options, function($a, $b) {
            return $a['label'] <=> $b['label'];
        });

        return $options;
    }

    public function getAdditionnalAttributes()
    {
        return array_merge(parent::getAdditionnalAttributes(), $this->additionnalAttributes);
    }
}
