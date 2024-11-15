<?php

namespace Probance\M2connector\Data;

class ArticleLangAttribute
{
    /**
     * Get article attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            [
                'magento_attribute' => 'id',
                'probance_attribute' => 'article_id',
                'field_type' => 'text',
                'position' => 1,
            ],
            [
                'magento_attribute' => 'locale',
                'probance_attribute' => 'article_area',
                'field_type' => 'text',
                'position' => 2,
            ],
            [
                'magento_attribute' => 'name',
                'probance_attribute' => 'nom_article',
                'field_type' => 'text',
                'position' => 3,
            ],
            [
                'magento_attribute' => 'description',
                'probance_attribute' => 'description_article',
                'field_type' => 'text',
                'position' => 4,
            ],
            [
                'magento_attribute' => 'product_url',
                'probance_attribute' => 'url_article',
                'field_type' => 'text',
                'position' => 5,
            ],
        ];
    }
}
