<?php

namespace Walkwizus\Probance\Data;

class OrderAttribute
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
                'magento_attribute' => 'qty_ordered',
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
                'magento_attribute' => 'order_id',
                'probance_attribute' => 'order_id',
                'field_type' => 'text',
                'position' => 7,
            ],
            [
                'magento_attribute' => 'quote_id',
                'probance_attribute' => 'cart_id',
                'field_type' => 'text',
                'position' => 8,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'origine',
                'field_type' => 'text',
                'user_value' => 'WEB',
                'position' => 9,
            ],
        ];
    }
}