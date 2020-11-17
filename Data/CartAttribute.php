<?php

namespace Probance\M2connector\Data;

class CartAttribute
{
    /**
     * Get order attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            [
                'magento_attribute' => 'customer_id',
                'probance_attribute' => 'customer_id',
                'field_type' => 'text',
                'position' => 1,
            ],
            [
                'magento_attribute' => 'created_at',
                'probance_attribute' => 'event_date',
                'field_type' => 'datetime',
                'position' => 2,
            ],
            [
                'magento_attribute' => 'product_id',
                'probance_attribute' => 'product_id',
                'field_type' => 'text',
                'position' => 3,
            ],
            [
                'magento_attribute' => 'child_id',
                'probance_attribute' => 'article_id',
                'field_type' => 'text',
                'position' => 4,
            ],
            [
                'magento_attribute' => 'qty',
                'probance_attribute' => 'quantity',
                'field_type' => 'text',
                'position' => 5,
            ],
            [
                'magento_attribute' => 'row_total_incl_tax',
                'probance_attribute' => 'amount_vat',
                'field_type' => 'price',
                'position' => 6,
            ],
            [
                'magento_attribute' => 'quote_id',
                'probance_attribute' => 'cart_id',
                'field_type' => 'text',
                'position' => 7,
            ],
        ];
    }
}