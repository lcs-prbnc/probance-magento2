<?php

namespace Probance\M2connector\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Probance\M2connector\Data\CouponAttribute;
use Probance\M2connector\Model\MappingCouponFactory;
use Probance\M2connector\Model\MappingProductFactory;
use Probance\M2connector\Model\MappingCustomerFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var MappingProductFactory
     */
    protected $mappingProductFactory;

    /**
     * @var MappingCustomerFactory
     */
    protected $mappingCustomerFactory;

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
     * @param MappingProductFactory $mappingProductFactory
     * @param MappingCustomerFactory $mappingCustomerFactory
     * @param MappingCouponFactory $mappingCouponFactory
     * @param CouponAttribute $couponAttribute
     */
    public function __construct(
        MappingProductFactory $mappingProductFactory,
        MappingCustomerFactory $mappingCustomerFactory,
        MappingCouponFactory $mappingCouponFactory,
        CouponAttribute $couponAttribute
    )
    {
        $this->mappingProductFactory = $mappingProductFactory;
        $this->mappingCustomerFactory = $mappingCustomerFactory;
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
        } else if (version_compare($context->getVersion(), '1.1.1', '<=')) {
            // Adding date_last_reappro in product flow
            $attribute = [
                'magento_attribute' => 'empty_field',
                'probance_attribute' => 'date_last_reappro',
                'field_type' => 'date',
                'position' => 18
            ]
            $this->mappingProductFactory
                ->create()
                ->setData($attribute)
                ->save();
            // Ensure locale is set for option_string1 in customer flow
            $this->mappingCustomerFactory
                ->create()
                ->load('option_string1','probance_attribute')
                ->setData('magento_attribute', 'locale')
                ->save();
        }
    }
}
