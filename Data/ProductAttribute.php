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
                'position' => 1,
            ],
            [
                'magento_attribute' => 'name',
                'probance_attribute' => 'product_name',
                'field_type' => 'text',
                'position' => 2,
            ],
            [
                'magento_attribute' => 'short_description',
                'probance_attribute' => 'product_short_desc',
                'field_type' => 'text',
                'position' => 3,
            ],
            [
                'magento_attribute' => 'product_url',
                'probance_attribute' => 'link_url',
                'field_type' => 'text',
                'position' => 4,
            ],
            [
                'magento_attribute' => 'image_url',
                'probance_attribute' => 'image_url',
                'field_type' => 'text',
                'position' => 5,
            ],
            [
                'magento_attribute' => 'created_at',
                'probance_attribute' => 'release_date',
                'field_type' => 'date',
                'position' => 6,
            ],
            [
                'magento_attribute' => 'news_from_date',
                'probance_attribute' => 'markdown_date',
                'field_type' => 'date',
                'position' => 7,
            ],
            [
                'magento_attribute' => 'price_incl_tax',
                'probance_attribute' => 'price1',
                'field_type' => 'price',
                'position' => 8,
            ],
            [
                'magento_attribute' => 'special_price_incl_tax',
                'probance_attribute' => 'price2',
                'field_type' => 'price',
                'position' => 9,
            ],
            [
                'magento_attribute' => 'price_excl_tax',
                'probance_attribute' => 'price3',
                'field_type' => 'price',
                'position' => 10,
            ],
            [
                'magento_attribute' => 'special_price_excl_tax',
                'probance_attribute' => 'price4',
                'field_type' => 'price',
                'position' => 11,
            ],
            [
                'magento_attribute' => 'qty',
                'probance_attribute' => 'stock',
                'field_type' => 'text',
                'position' => 12,
            ],
            [
                'magento_attribute' => 'category1',
                'probance_attribute' => 'category1',
                'field_type' => 'text',
                'position' => 13,
            ],
            [
                'magento_attribute' => 'category2',
                'probance_attribute' => 'category2',
                'field_type' => 'text',
                'position' => 14,
            ],
            [
                'magento_attribute' => 'category3',
                'probance_attribute' => 'category3',
                'field_type' => 'text',
                'position' => 15,
            ],
            [
                'magento_attribute' => 'sku',
                'probance_attribute' => 'product_string1',
                'field_type' => 'text',
                'position' => 16,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'product_string2',
                'field_type' => 'text',
                'position' => 17,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'product_string3',
                'field_type' => 'text',
                'position' => 18,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'product_list1',
                'field_type' => 'text',
                'position' => 19,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'product_list2',
                'field_type' => 'text',
                'position' => 20,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'date_last_reappro',
                'field_type' => 'date',
                'position' => 21,
            ],
        ];
    }
}
