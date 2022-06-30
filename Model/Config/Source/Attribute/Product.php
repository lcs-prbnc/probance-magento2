<?php

namespace Probance\M2connector\Model\Config\Source\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class Product implements OptionSourceInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * Excluded attributes (Magento system)
     *
     * @var array
     */
    private $attributesExcluded = [
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'custom_layout_update',
        'page_layout',
        'category_ids',
        'options_container',
        'image',
        'image_label',
        'small_image',
        'small_image_label',
        'thumbnail',
        'thumbnail_label',
        'custom_layout',
        'links_purchased_separately',
        'swatch_image',
        'shipment_type',
        'price_type',
        'price_view',
        'tax_class',
        'tier_price',
        'url_key',
        'sku_type',
        'weight_type',
        'links_title',
        'media_gallery',
        'gallery',
        'msrp_display_actual_price_type',
    ];

    protected $additionnalAttributes = [
        [
            'label' => 'Empty Field',
            'value' => 'empty_field',
        ],
        [
            'label' => 'Categories',
            'value' => 'categories',
        ],
        [
            'label' => 'User Value',
            'value' => 'user_value',
        ],
        [
            'label' => 'Image URL',
            'value' => 'image_url',
        ],
        [
            'label' => 'Product URL',
            'value' => 'product_url',
        ],
        [
            'label' => 'Product ID',
            'value' => 'id',
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
            'label' => 'Is In Stock',
            'value' => 'is_in_stock',
        ],
        [
            'label' => 'Price Incl Tax',
            'value' => 'price_incl_tax',
        ],
        [
            'label' => 'Price Excl Tax',
            'value' => 'price_excl_tax',
        ],
        [
            'label' => 'Special Price Excl Tax',
            'value' => 'special_price_excl_tax',
        ],
        [
            'label' => 'Special Price Incl Tax',
            'value' => 'special_price_incl_tax',
        ],
    ];

    /**
     * Attributes constructor.
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
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );

        $options = [];
        
        foreach ($attributeRepository->getItems() as $attribute) {
            if (!in_array($attribute->getAttributeCode(), $this->attributesExcluded)) {
                if ($attribute->getAttributeCode() && $attribute->getFrontendLabel()) {
                    $options[] = array(
                        'value' => $attribute->getAttributeCode(),
                        'label' => $attribute->getFrontendLabel(),
                    );
                }
            }
        }

        $optionsMerged = array_merge($options, $this->additionnalAttributes);

        usort($optionsMerged, function($a, $b) {
            return $a['label'] <=> $b['label'];
        });

        return $optionsMerged;
    }
}