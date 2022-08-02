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
            ],
            [
                'label' => 'First category',
                'value' => 'category1',
            ],
            [
                'label' => 'Second category',
                'value' => 'category2',
            ],
            [
                'label' => 'Third category',
                'value' => 'category3',
            ],
        ]);

        usort($options, function($a, $b) {
            return $a['label'] <=> $b['label'];
        });

        return $options;
    }
}
