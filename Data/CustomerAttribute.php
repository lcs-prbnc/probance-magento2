<?php

namespace Walkwizus\Probance\Data;

class CustomerAttribute
{
    /**
     * Get customer attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            [
                'magento_attribute' => 'id',
                'probance_attribute' => 'customer_id',
                'field_type' => 'text',
                'position' => 1,
            ],
            [
                'magento_attribute' => 'firstname',
                'probance_attribute' => 'name1',
                'field_type' => 'text',
                'position' => 2,
            ],
            [
                'magento_attribute' => 'lastname',
                'probance_attribute' => 'name2',
                'field_type' => 'text',
                'position' => 3,
            ],
            [
                'magento_attribute' => 'email',
                'probance_attribute' => 'email',
                'field_type' => 'text',
                'position' => 4,
            ],
            [
                'magento_attribute' => 'dob',
                'probance_attribute' => 'birthday',
                'field_type' => 'date',
                'position' => 5
            ],
            [
                'magento_attribute' => 'created_at',
                'probance_attribute' => 'registration_date',
                'field_type' => 'text',
                'position' => 6,
            ],
            [
                'magento_attribute' => 'optin_flag',
                'probance_attribute' => 'optin_flag',
                'field_type' => 'text',
                'position' => 7,
            ],
            [
                'magento_attribute' => 'gender',
                'probance_attribute' => 'gender',
                'field_type' => 'text',
                'position' => 8,
            ],
            [
                'magento_attribute' => 'billing_address_country',
                'probance_attribute' => 'area',
                'field_type' => 'text',
                'position' => 9,
            ],
            [
                'magento_attribute' => 'billing_address_city',
                'probance_attribute' => 'city',
                'field_type' => 'text',
                'position' => 10,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'segmentation',
                'field_type' => 'text',
                'position' => 11,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_string1',
                'field_type' => 'text',
                'position' => 12,
            ],
            [
                'magento_attribute' => 'billing_address_postcode',
                'probance_attribute' => 'option_string2',
                'field_type' => 'text',
                'position' => 13,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_string3',
                'field_type' => 'text',
                'position' => 14,
            ],
            [
                'magento_attribute' => 'billing_address_phone',
                'probance_attribute' => 'option_string4',
                'field_type' => 'text',
                'position' => 15,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_string5',
                'field_type' => 'text',
                'position' => 16,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_num1',
                'field_type' => 'text',
                'position' => 17,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_num2',
                'field_type' => 'text',
                'position' => 18,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_num3',
                'field_type' => 'text',
                'position' => 19,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_num4',
                'field_type' => 'text',
                'position' => 20,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_num5',
                'field_type' => 'text',
                'position' => 21,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_date1',
                'field_type' => 'date',
                'position' => 22,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_date2',
                'field_type' => 'date',
                'position' => 23,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_date3',
                'field_type' => 'date',
                'position' => 24,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_datetime1',
                'field_type' => 'datetime',
                'position' => 25,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'option_datetime2',
                'field_type' => 'datetime',
                'position' => 26,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'subscription_flag',
                'field_type' => 'text',
                'position' => 27,
            ],
            [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'repoussoir',
                'field_type' => 'text',
                'position' => 28,
            ],
        ];
    }
}