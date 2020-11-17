<?php

namespace Probance\M2connector\Data;

class ProductAttribute
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
                'probance_attribute' => 'product_id',
                'field_type' => 'text',
                'position' => 1
            ],
            [
                'magento_attribute' => 'name',
                'probance_attribute' => 'product_name',
                'field_type' => 'text',
                'position' => 2
            ],
            [
                'magento_attribute' => 'short_description',
                'probance_attribute' => 'product_short_desc',
                'field_type' => 'text',
                'position' => 3
            ],
            [
                'magento_attribute' => 'product_url',
                'probance_attribute' => 'link_url',
                'field_type' => 'text',
                'position' => 4
            ],
            [
                'magento_attribute' => 'image_url',
                'probance_attribute' => 'image_url',
                'field_type' => 'text',
                'position' => 5
            ],
            [
                'magento_attribute' => 'created_at',
                'probance_attribute' => 'release_date',
                'field_type' => 'date',
                'position' => 6
            ],
            [
                'magento_attribute' => 'news_from_date',
                'probance_attribute' => 'markdown_date',
                'field_type' => 'date',
                'position' => 7
            ],
            [
                'magento_attribute' => 'price',
                'probance_attribute' => 'price1',
                'field_type' => 'price',
                'position' => 8
            ],
            [
                'magento_attribute' => 'special_price',
                'probance_attribute' => 'price2',
                'field_type' => 'price',
                'position' => 9
            ],
            [
                'magento_attribute' => 'price',
                'probance_attribute' => 'price3',
                'field_type' => 'price',
                'position' => 10
            ],
            [
                'magento_attribute' => 'is_in_stock',
                'probance_attribute' => 'stock',
                'field_type' => 'text',
                'position' => 11
            ],
            [
                'magento_attribute' => 'categories',
                'probance_attribute' => 'categories',
                'field_type' => 'text',
                'position' => 12
            ],
            [
                'magento_attribute' => 'sku',
                'probance_attribute' => 'product_string1',
                'field_type' => 'text',
                'position' => 15
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'product_string2',
                'field_type' => 'text',
                'position' => 16
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'product_string3',
                'field_type' => 'text',
                'position' => 17
            ],
        ];
    }
}