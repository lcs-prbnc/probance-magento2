<?php

namespace Probance\M2connector\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Probance\M2connector\Data\ArticleAttribute;
use Probance\M2connector\Data\CartAttribute;
use Probance\M2connector\Data\CustomerAttribute;
use Probance\M2connector\Data\OrderAttribute;
use Probance\M2connector\Data\ProductAttribute;
use Probance\M2connector\Model\MappingArticleFactory;
use Probance\M2connector\Model\MappingCustomerFactory;
use Probance\M2connector\Model\MappingProductFactory;
use Probance\M2connector\Model\MappingOrderFactory;
use Probance\M2connector\Model\MappingCartFactory;

class InstallData implements InstallDataInterface
{
    /**
     * @var MappingCustomerFactory
     */
    private $mappingCustomerFactory;

    /**
     * @var CustomerAttribute
     */
    private $customerAttribute;

    /**
     * @var MappingProductFactory
     */
    private $mappingProductFactory;

    /**
     * @var ProductAttribute
     */
    private $productAttribute;

    /**
     * @var MappingOrderFactory
     */
    private $mappingOrderFactory;

    /**
     * @var OrderAttribute
     */
    private $orderAttribute;

    /**
     * @var MappingArticleFactory
     */
    private $mappingArticleFactory;

    /**
     * @var ArticleAttribute
     */
    private $articleAttribute;

    /**
     * @var MappingCartFactory
     */
    private $mappingCartFactory;

    /**
     * @var CartAttribute
     */
    private $cartAttribute;

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
        MappingCustomerFactory $mappingCustomerFactory,
        CustomerAttribute $customerAttribute,
        MappingProductFactory $mappingProductFactory,
        ProductAttribute $productAttribute,
        MappingArticleFactory $mappingArticleFactory,
        ArticleAttribute $articleAttribute,
        MappingOrderFactory $mappingOrderFactory,
        OrderAttribute $orderAttribute,
        MappingCartFactory $mappingCartFactory,
        CartAttribute $cartAttribute
    )
    {
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
    }

    /**
     * Install Data
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
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
    }
}

