<?php

namespace Probance\M2connector\Data;

class CouponAttribute
{
    /**
     * Get coupon attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            [
                'magento_attribute' => 'rule.name',
                'probance_attribute' => 'rule_name',
                'field_type' => 'text',
                'position' => 1,
            ],
            [
                'magento_attribute' => 'code',
                'probance_attribute' => 'coupon_code',
                'field_type' => 'text',
                'position' => 2,
            ],
            [
                'magento_attribute' => 'rule.from_date',
                'probance_attribute' => 'start_date',
                'field_type' => 'datetime',
                'position' => 3,
            ],
            [
                'magento_attribute' => 'rule.to_date',
                'probance_attribute' => 'end_date',
                'field_type' => 'datetime',
                'position' => 4,
            ],
        ];
    }
}
