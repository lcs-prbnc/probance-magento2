<?php

namespace Probance\M2connector\Model\Config\Source\Attribute;

use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class Customer implements OptionSourceInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

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
            'value' => 'id'
        ],
        [
            'label' => 'Customer Group',
            'value' => 'customer_group_code',
        ],
        [
            'label' => 'Optin Flag (Newsletter Subscriber)',
            'value' => 'optin_flag'
        ],
        [
            'label' => 'Billing Address Company',
            'value' => 'billing_address_company'
        ],
        [
            'label' => 'Billing Address City',
            'value' => 'billing_address_city'
        ],
        [
            'label' => 'Billing Address Country',
            'value' => 'billing_address_country'
        ],
        [
            'label' => 'Billing Address State',
            'value' => 'billing_address_state'
        ],
        [
            'label' => 'Billing Address Postcode',
            'value' => 'billing_address_postcode'
        ],
        [
            'label' => 'Billing Address Phone',
            'value' => 'billing_address_phone'
        ],
        [
            'label' => 'Shipping Address Company',
            'value' => 'shipping_address_company'
        ],
        [
            'label' => 'Shipping Address City',
            'value' => 'shipping_address_city'
        ],
        [
            'label' => 'Shipping Address Country',
            'value' => 'shipping_address_country'
        ],
        [
            'label' => 'Shipping Address State',
            'value' => 'shipping_address_state'
        ],
        [
            'label' => 'Shipping Address Postcode',
            'value' => 'billing_address_postcode'
        ],
        [
            'label' => 'Shipping Address Phone',
            'value' => 'shipping_address_phone'
        ],
        [
            'label' => 'Locale used',
            'value' => 'locale'
        ]
    ];

    /**
     * Attribute constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Retrieve attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $attributeRepository = $this->attributeRepository->getList(
            CustomerModel::ENTITY,
            $searchCriteria
        );

        $options = [];

        foreach ($attributeRepository->getItems() as $attribute) {
            if ($attribute->getAttributeCode() && $attribute->getFrontendLabel()) {
                $options[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontendLabel(),
                );
            }
        }

        $options = array_merge($options, $this->additionnalAttributes);

        usort($options, function($a, $b) {
            return $a['label'] <=> $b['label'];
        });

        return $options;
    }
}
