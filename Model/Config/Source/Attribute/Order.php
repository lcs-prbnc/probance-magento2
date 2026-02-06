<?php

namespace Probance\M2connector\Model\Config\Source\Attribute;

use Magento\Framework\Data\OptionSourceInterface;

class Order extends AbstractAttribute implements OptionSourceInterface
{
    const CACHE_NAME = 'Order';

    /**
     * @var array
     */
    protected $additionnalAttributes = [
        [
            'label' => 'Empty Field',
            'value' => 'empty_field',
        ],
        [
            'label' => 'Customer ID',
            'value' => 'customer_id',
        ],
        [
            'label' => 'Email',
            'value' => 'customer_email',
        ],
        [
            'label' => 'Origin',
            'value' => 'origin'
        ],
        [
            'label' => 'Article ID',
            'value' => 'child_id'
        ],
        [
            'label' => 'Item ID',
            'value' => 'item_id',
        ],
        [
            'label' => 'Order ID',
            'value' => 'order_id',
        ],
        [
            'label' => 'Quote ID (Cart)',
            'value' => 'quote_id',
        ],
        [
            'label' => 'Parent Item ID',
            'value' => 'parent_item_id',
        ],
        [
            'label' => 'Store ID',
            'value' => 'store_id',
        ],
        [
            'label' => 'Created At',
            'value' => 'created_at',
        ],
        [
            'label' => 'Updated At',
            'value' => 'updated_at',
        ],
        [
            'label' => 'Product ID',
            'value' => 'product_id',
        ],
        [
            'label' => 'Product Type',
            'value' => 'product_type',
        ],
        [
            'label' => 'Product Options',
            'value' => 'product_options',
        ],
        [
            'label' => 'Weight',
            'value' => 'weight',
        ],
        [
            'label' => 'Is Virtual',
            'value' => 'is_virtual',
        ],
        [
            'label' => 'Sku',
            'value' => 'sku',
        ],
        [
            'label' => 'Name',
            'value' => 'name',
        ],
        [
            'label' => 'Description',
            'value' => 'description',
        ],
        [
            'label' => 'Is Qty Decimal',
            'value' => 'is_qty_decimal',
        ],
        [
            'label' => 'Qty Backordered',
            'value' => 'qty_backordered',
        ],
        [
            'label' => 'Qty Canceled',
            'value' => 'qty_canceled',
        ],
        [
            'label' => 'Qty Invoiced',
            'value' => 'qty_invoiced',
        ],
        [
            'label' => 'Qty Ordered',
            'value' => 'qty_ordered',
        ],
        [
            'label' => 'Qty Refunded',
            'value' => 'qty_refunded',
        ],
        [
            'label' => 'Qty Shipped',
            'value' => 'qty_shipped',
        ],
        [
            'label' => 'Price',
            'value' => 'price',
        ],
        [
            'label' => 'Base Price',
            'value' => 'base_price',
        ],
        [
            'label' => 'Original Price',
            'value' => 'original_price',
        ],
        [
            'label' => 'Base Original Price',
            'value' => 'base_original_price',
        ],
        [
            'label' => 'Tax Percent',
            'value' => 'tax_percent',
        ],
        [
            'label' => 'Tax Amount',
            'value' => 'tax_amount',
        ],
        [
            'label' => 'Base Tax Amount',
            'value' => 'base_tax_amount',
        ],
        [
            'label' => 'Tax Invoiced',
            'value' => 'tax_invoiced',
        ],
        [
            'label' => 'Base Tax Invoiced',
            'value' => 'base_tax_invoiced',
        ],
        [
            'label' => 'Discount Percent',
            'value' => 'discount_percent',
        ],
        [
            'label' => 'Discount Amount',
            'value' => 'discount_amount',
        ],
        [
            'label' => 'Base Discount Amount',
            'value' => 'base_discount_amount',
        ],
        [
            'label' => 'Discount Invoiced',
            'value' => 'discount_invoiced',
        ],
        [
            'label' => 'Base Discount Invoiced',
            'value' => 'base_discount_invoiced',
        ],
        [
            'label' => 'Amount Refunded',
            'value' => 'amount_refunded',
        ],
        [
            'label' => 'Base Amount Refunded',
            'value' => 'base_amount_refunded',
        ],
        [
            'label' => 'Row Total',
            'value' => 'row_total',
        ],
        [
            'label' => 'Base Row Total',
            'value' => 'base_row_total',
        ],
        [
            'label' => 'Row Invoiced',
            'value' => 'row_invoiced',
        ],
        [
            'label' => 'Base Row Invoiced',
            'value' => 'base_row_invoiced',
        ],
        [
            'label' => 'Row Weight',
            'value' => 'row_weight',
        ],
        [
            'label' => 'Base Tax Before Discount',
            'value' => 'base_tax_before_discount',
        ],
        [
            'label' => 'Tax Before Discount',
            'value' => 'tax_before_discount',
        ],
        [
            'label' => 'Price Incl Tax',
            'value' => 'price_incl_tax',
        ],
        [
            'label' => 'Base Price Incl Tax',
            'value' => 'base_price_incl_tax',
        ],
        [
            'label' => 'Row Total Incl Tax',
            'value' => 'row_total_incl_tax',
        ],
        [
            'label' => 'Base Row Total Incl Tax',
            'value' => 'base_row_total_incl_tax',
        ],
        [
            'label' => 'Tax Canceled',
            'value' => 'tax_canceled',
        ],
        [
            'label' => 'Tax Refunded',
            'value' => 'tax_refunded',
        ],
        [
            'label' => 'Discount Refunded',
            'value' => 'discount_refunded',
        ],
        [
            'label' => 'Free Shipping',
            'value' => 'free_shipping',
        ]
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionsMerged = $this->loadAttributeArray();
        if (!$optionsMerged) {

            $optionsMerged = $this->getAdditionnalAttributes();

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
        return $this->additionnalAttributes;
    }
}
