<?php

namespace Probance\M2connector\Data;

class ArticleTierPriceAttribute
{
    /**
     * Get product attribute
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
                'magento_attribute' => 'tier_price_customer_group_code',
                'probance_attribute' => 'article_group_prix',
                'field_type' => 'text',
                'position' => 2,
            ],
            [
                'magento_attribute' => 'tier_price_customer_group_id',
                'probance_attribute' => 'id_group',
                'field_type' => 'text',
                'position' => 3,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'date_promo',
                'field_type' => 'date',
                'position' => 4,
            ],
            [
                'magento_attribute' => 'price_incl_tax',
                'probance_attribute' => 'prix_ttc',
                'field_type' => 'price',
                'position' => 5,
            ],
            [
                'magento_attribute' => 'tier_price_value',
                'probance_attribute' => 'prix_promo',
                'field_type' => 'price',
                'position' => 6,
            ],
            [
                'magento_attribute' => 'price_excl_tax',
                'probance_attribute' => 'prix_ht',
                'field_type' => 'price',
                'position' => 7,
            ],
        ];
    }
}
