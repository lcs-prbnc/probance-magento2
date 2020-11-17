<?php

namespace Probance\M2connector\Model\Config\Source\Attribute;

class Article extends Product
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array_merge(parent::toOptionArray(), [
            [
                'label' => 'Parent ID (Configurable Product ID)',
                'value' => 'parent_id',
            ]
        ]);

        usort($options, function($a, $b) {
            return $a['label'] <=> $b['label'];
        });

        return $options;
    }
}