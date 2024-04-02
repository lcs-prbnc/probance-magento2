<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Probance\M2connector\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

use Probance\M2connector\Data\ArticleAttribute;
use Probance\M2connector\Data\CartAttribute;
use Probance\M2connector\Data\CustomerAttribute;
use Probance\M2connector\Data\OrderAttribute;
use Probance\M2connector\Data\ProductAttribute;
use Probance\M2connector\Data\CouponAttribute;
use Probance\M2connector\Model\MappingArticleFactory;
use Probance\M2connector\Model\MappingCustomerFactory;
use Probance\M2connector\Model\MappingProductFactory;
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
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param MappingCustomerFactory $mappingCustomerFactory
     * @param CustomerAttribute $customerAttribute
     * @param MappingProductFactory $mappingProductFactory
     * @param ProductAttribute $productAttribute
     * @param MappingArticleFactory $mappingArticleFactory
     * @param ArticleAttribute $articleAttribute
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
        MappingArticleFactory $mappingArticleFactory,
        ArticleAttribute $articleAttribute,
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
        $this->mappingArticleFactory = $mappingArticleFactory;
        $this->articleAttribute = $articleAttribute;
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
        foreach ($this->customerAttribute->getAttributes() as $attribute) {
            $this->mappingCustomerFactory
                ->create()
                ->setData($attribute)
                ->save();
        }

        foreach ($this->productAttribute->getAttributes() as $attribute) {
            $this->mappingProductFactory
                ->create()
                ->setData($attribute)
                ->save();
        }

        foreach ($this->articleAttribute->getAttributes() as $attribute) {
            $this->mappingArticleFactory
                ->create()
                ->setData($attribute)
                ->save();
        }

        foreach ($this->orderAttribute->getAttributes() as $attribute) {
            $this->mappingOrderFactory
                ->create()
                ->setData($attribute)
                ->save();
        }

        foreach ($this->cartAttribute->getAttributes() as $attribute) {
            $this->mappingCartFactory
                ->create()
                ->setData($attribute)
                ->save();
        }
        foreach ($this->couponAttribute->getAttributes() as $attribute) {
            $this->mappingCouponFactory
                ->create()
                ->setData($attribute)
                ->save();
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
