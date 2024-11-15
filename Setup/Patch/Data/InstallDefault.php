<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Probance\M2connector\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

use Probance\M2connector\Data\ArticleAttribute;
use Probance\M2connector\Data\ArticleLangAttribute;
use Probance\M2connector\Data\ArticleTierPriceAttribute;
use Probance\M2connector\Data\CartAttribute;
use Probance\M2connector\Data\CustomerAttribute;
use Probance\M2connector\Data\OrderAttribute;
use Probance\M2connector\Data\ProductAttribute;
use Probance\M2connector\Data\ProductLangAttribute;
use Probance\M2connector\Data\ProductTierPriceAttribute;
use Probance\M2connector\Data\CouponAttribute;
use Probance\M2connector\Model\MappingArticleFactory;
use Probance\M2connector\Model\MappingArticleLangFactory;
use Probance\M2connector\Model\MappingArticleTierPriceFactory;
use Probance\M2connector\Model\MappingCustomerFactory;
use Probance\M2connector\Model\MappingProductFactory;
use Probance\M2connector\Model\MappingProductLangFactory;
use Probance\M2connector\Model\MappingProductTierPriceFactory;
use Probance\M2connector\Model\MappingOrderFactory;
use Probance\M2connector\Model\MappingCartFactory;
use Probance\M2connector\Model\MappingCouponFactory;

class InstallDefault implements DataPatchInterface
{
    /**
     * @var MappingCustomerFactory
     */
    protected $mappingCustomerFactory;

    /**
     * @var CustomerAttribute
     */
    protected $customerAttribute;

    /**
     * @var MappingProductFactory
     */
    protected $mappingProductFactory;

    /**
     * @var ProductAttribute
     */
    protected $productAttribute;

    /**
     * @var MappingProductLangFactory
     */
    protected $mappingProductLangFactory;

    /**
     * @var ProductLangAttribute
     */
    protected $productLangAttribute;

    /**
     * @var MappingProductTierPriceFactory
     */
    protected $mappingProductTierPriceFactory;

    /**
     * @var ProductTierPriceAttribute
     */
    protected $productTierPriceAttribute;

    /**
     * @var MappingOrderFactory
     */
    protected $mappingOrderFactory;

    /**
     * @var OrderAttribute
     */
    protected $orderAttribute;

    /**
     * @var MappingArticleFactory
     */
    protected $mappingArticleFactory;

    /**
     * @var ArticleAttribute
     */
    protected $articleAttribute;

    /**
     * @var MappingArticleLangFactory
     */
    protected $mappingArticleLangFactory;

    /**
     * @var ArticleLangAttribute
     */
    protected $articleLangAttribute;

    /**
     * @var MappingArticleTierPriceFactory
     */
    protected $mappingArticleTierPriceFactory;

    /**
     * @var ArticleTierPriceAttribute
     */
    protected $articleTierPriceAttribute;

    /**
     * @var MappingCartFactory
     */
    protected $mappingCartFactory;

    /**
     * @var CartAttribute
     */
    protected $cartAttribute;

    /**
     * @var MappingCouponFactory
     */
    protected $mappingCouponFactory;

    /**
     * @var CouponAttribute
     */
    protected $couponAttribute;

    /**
     * @param MappingCustomerFactory $mappingCustomerFactory
     * @param CustomerAttribute $customerAttribute
     * @param MappingProductFactory $mappingProductFactory
     * @param ProductAttribute $productAttribute
     * @param MappingProductLangFactory $mappingProductLangFactory
     * @param ProductLangAttribute $productLangAttribute
     * @param MappingProductTierPriceFactory $mappingProductTierPriceFactory
     * @param ProductTierPriceAttribute $productTierPriceAttribute
     * @param MappingArticleFactory $mappingArticleFactory
     * @param ArticleAttribute $articleAttribute
     * @param MappingArticleLangFactory $mappingArticleLangFactory
     * @param ArticleLangAttribute $articleLangAttribute
     * @param MappingArticleTierPriceFactory $mappingArticleTierPriceFactory
     * @param ArticleTierPriceAttribute $articleTierPriceAttribute
     * @param MappingOrderFactory $mappingOrderFactory
     * @param OrderAttribute $orderAttribute
     * @param MappingCartFactory $mappingCartFactory
     * @param CartAttribute $cartAttribute
     * @param MappingCouponFactory $mappingCouponFactory
     * @param CouponAttribute $couponAttribute
     */
    public function __construct(
        MappingCustomerFactory $mappingCustomerFactory,
        CustomerAttribute $customerAttribute,
        MappingProductFactory $mappingProductFactory,
        ProductAttribute $productAttribute,
        MappingProductLangFactory $mappingProductLangFactory,
        ProductLangAttribute $productLangAttribute,
        MappingProductTierPriceFactory $mappingProductTierPriceFactory,
        ProductTierPriceAttribute $productTierPriceAttribute,
        MappingArticleFactory $mappingArticleFactory,
        ArticleAttribute $articleAttribute,
        MappingArticleLangFactory $mappingArticleLangFactory,
        ArticleLangAttribute $articleLangAttribute,
        MappingArticleTierPriceFactory $mappingArticleTierPriceFactory,
        ArticleTierPriceAttribute $articleTierPriceAttribute,
        MappingOrderFactory $mappingOrderFactory,
        OrderAttribute $orderAttribute,
        MappingCartFactory $mappingCartFactory,
        CartAttribute $cartAttribute,
        MappingCouponFactory $mappingCouponFactory,
        CouponAttribute $couponAttribute
    ) {
        $this->mappingCustomerFactory = $mappingCustomerFactory;
        $this->customerAttribute = $customerAttribute;
        $this->mappingProductFactory = $mappingProductFactory;
        $this->productAttribute = $productAttribute;
        $this->mappingProductLangFactory = $mappingProductLangFactory;
        $this->productLangAttribute = $productLangAttribute;
        $this->mappingProductTierPriceFactory = $mappingProductTierPriceFactory;
        $this->productTierPriceAttribute = $productTierPriceAttribute;
        $this->mappingArticleFactory = $mappingArticleFactory;
        $this->articleAttribute = $articleAttribute;
        $this->mappingArticleLangFactory = $mappingArticleLangFactory;
        $this->articleLangAttribute = $articleLangAttribute;
        $this->mappingArticleTierPriceFactory = $mappingArticleTierPriceFactory;
        $this->articleTierPriceAttribute = $articleTierPriceAttribute;
        $this->mappingOrderFactory = $mappingOrderFactory;
        $this->orderAttribute = $orderAttribute;
        $this->mappingCartFactory = $mappingCartFactory;
        $this->cartAttribute = $cartAttribute;
        $this->mappingCouponFactory = $mappingCouponFactory;
        $this->couponAttribute = $couponAttribute;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function apply()
    {
        $mappings = [
            $this->customerAttribute => $this->mappingCustomerFactory,
            $this->productAttribute => $this->mappingProductFactory,
            $this->productLangAttribute => $this->mappingProductLangFactory,
            $this->productTierPriceAttribute => $this->mappingProductTierPriceFactory,
            $this->articleAttribute => $this->mappingArticleFactory,
            $this->articleLangAttribute => $this->mappingArticleLangFactory,
            $this->articleTierPriceAttribute => $this->mappingArticleTierPriceFactory,
            $this->orderAttribute => $this->mappingOrderFactory,
            $this->cartAttribute => $this->mappingCartFactory,
            $this->couponAttribute => $this->mappingCouponFactory
        ];
            
        foreach ($mappings as $attrObj => $mappingFactory) {
            foreach ($attrObj->getAttributes() as $attribute) {
                $mappingFactory->create()
                    ->setData($attribute)
                    ->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
