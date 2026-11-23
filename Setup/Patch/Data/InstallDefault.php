<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Probance\M2connector\Setup\Patch\Data;

use Probance\M2connector\Model\ResourceModel\MappingCoupon as MappingCouponResource;
use Probance\M2connector\Model\ResourceModel\MappingCart as MappingCartResource;
use Probance\M2connector\Model\ResourceModel\MappingOrder as MappingOrderResource;
use Probance\M2connector\Model\ResourceModel\MappingArticleTierPrice as MappingArticleTierPriceResource;
use Probance\M2connector\Model\ResourceModel\MappingArticleLang as MappingArticleLangResource;
use Probance\M2connector\Model\ResourceModel\MappingArticle as MappingArticleResource;
use Probance\M2connector\Model\ResourceModel\MappingProductTierPrice as MappingProductTierPriceResource;
use Probance\M2connector\Model\ResourceModel\MappingProductLang as MappingProductLangResource;
use Probance\M2connector\Model\ResourceModel\MappingProduct as MappingProductResource;
use Probance\M2connector\Model\ResourceModel\MappingCustomer as MappingCustomerResource;
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
    /** @var MappingCouponResource */
    protected $mappingCouponResource;

    /** @var MappingCartResource */
    protected $mappingCartResource;

    /** @var MappingOrderResource */
    protected $mappingOrderResource;

    /** @var MappingArticleTierPriceResource */
    protected $mappingArticleTierPriceResource;

    /** @var MappingArticleLangResource */
    protected $mappingArticleLangResource;

    /** @var MappingArticleResource */
    protected $mappingArticleResource;

    /** @var MappingProductTierPriceResource */
    protected $mappingProductTierPriceResource;

    /** @var MappingProductLangResource */
    protected $mappingProductLangResource;

    /** @var MappingProductResource */
    protected $mappingProductResource;

    /** @var MappingCustomerResource */
    protected $mappingCustomerResource;

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
     * @param MappingCustomerResource $mappingCustomerResource
     * @param MappingProductResource $mappingProductResource
     * @param MappingProductLangResource $mappingProductLangResource
     * @param MappingProductTierPriceResource $mappingProductTierPriceResource
     * @param MappingArticleResource $mappingArticleResource
     * @param MappingArticleLangResource $mappingArticleLangResource
     * @param MappingArticleTierPriceResource $mappingArticleTierPriceResource
     * @param MappingOrderResource $mappingOrderResource
     * @param MappingCartResource $mappingCartResource
     * @param MappingCouponResource $mappingCouponResource
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
        CouponAttribute $couponAttribute,
        MappingCustomerResource $mappingCustomerResource,
        MappingProductResource $mappingProductResource,
        MappingProductLangResource $mappingProductLangResource,
        MappingProductTierPriceResource $mappingProductTierPriceResource,
        MappingArticleResource $mappingArticleResource,
        MappingArticleLangResource $mappingArticleLangResource,
        MappingArticleTierPriceResource $mappingArticleTierPriceResource,
        MappingOrderResource $mappingOrderResource,
        MappingCartResource $mappingCartResource,
        MappingCouponResource $mappingCouponResource
    ) {
        $this->mappingCouponResource = $mappingCouponResource;
        $this->mappingCartResource = $mappingCartResource;
        $this->mappingOrderResource = $mappingOrderResource;
        $this->mappingArticleTierPriceResource = $mappingArticleTierPriceResource;
        $this->mappingArticleLangResource = $mappingArticleLangResource;
        $this->mappingArticleResource = $mappingArticleResource;
        $this->mappingProductTierPriceResource = $mappingProductTierPriceResource;
        $this->mappingProductLangResource = $mappingProductLangResource;
        $this->mappingProductResource = $mappingProductResource;
        $this->mappingCustomerResource = $mappingCustomerResource;
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
            'customer' => [$this->customerAttribute, $this->mappingCustomerFactory, $this->mappingCustomerResource],
            'productAtt' => [$this->productAttribute, $this->mappingProductFactory, $this->mappingProductResource],
            'productLang' => [$this->productLangAttribute, $this->mappingProductLangFactory, $this->mappingProductLangResource],
            'productTierPrice' => [$this->productTierPriceAttribute, $this->mappingProductTierPriceFactory, $this->mappingProductTierPriceResource],
            'article' => [$this->articleAttribute, $this->mappingArticleFactory, $this->mappingArticleResource],
            'articleLang' => [$this->articleLangAttribute, $this->mappingArticleLangFactory, $this->mappingArticleLangResource],
            'articleTierPrice' => [$this->articleTierPriceAttribute, $this->mappingArticleTierPriceFactory, $this->mappingArticleTierPriceResource],
            'order' => [$this->orderAttribute, $this->mappingOrderFactory, $this->mappingOrderResource],
            'cart' => [$this->cartAttribute, $this->mappingCartFactory, $this->mappingCartResource],
            'coupon' => [$this->couponAttribute, $this->mappingCouponFactory, $this->mappingCouponResource]
        ];

        foreach ($mappings as $mappingObjs) {
            foreach ($mappingObjs[0]->getAttributes() as $attribute) {
                $model = $mappingObjs[1]->create()->setData($attribute);
                $mappingObjs[2]->save($model);
            }
        }
        return $this;
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
