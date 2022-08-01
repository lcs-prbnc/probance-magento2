<?php

namespace Probance\M2connector\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Probance\M2connector\Data\CouponAttribute;
use Probance\M2connector\Model\MappingCouponFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var MappingCouponFactory
     */
    protected $mappingCouponFactory;

    /**
     * @var CouponAttribute
     */
    protected $couponAttribute;

    /**
     * InstallData constructor.
     *
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
     */
    public function __construct(
        MappingCouponFactory $mappingCouponFactory,
        CouponAttribute $couponAttribute
    )
    {
        $this->mappingCouponFactory = $mappingCouponFactory;
        $this->couponAttribute = $couponAttribute;
    }

    /**
     * Install Data
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.12', '<')) {
            foreach ($this->couponAttribute->getAttributes() as $attribute) {
                $this->mappingCouponFactory
                    ->create()
                    ->setData($attribute)
                    ->save();
            }
        }
    }
}
