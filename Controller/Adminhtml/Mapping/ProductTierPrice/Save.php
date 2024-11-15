<?php

namespace Probance\M2connector\Controller\Adminhtml\Mapping\ProductTierPrice;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Probance\M2connector\Model\MappingProductTierPriceFactory;
use Probance\M2connector\Model\ResourceModel\MappingProductTierPriceFactory as MappingProductTierPriceResourceFactory;

class Save extends Action
{
    /**
     * @var MappingProductTierPriceFactory
     */
    protected $mappingProductTierPriceFactory;

    /**
     * @var MappingProductTierPriceResourceFactory
     */
    protected $mappingProductTierPriceResource;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param MappingProductTierPriceFactory $mappingProductTierPriceFactory
     * @param MappingProductTierPriceResourceFactory $mappingProductTierPriceResource
     */
    public function __construct(
        Action\Context $context,
        MappingProductTierPriceFactory $mappingProductTierPriceFactory,
        MappingProductTierPriceResourceFactory $mappingProductTierPriceResource
    )
    {
        parent::__construct($context);
        $this->mappingProductTierPriceFactory = $mappingProductTierPriceFactory;
        $this->mappingProductTierPriceResource = $mappingProductTierPriceResource;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Probance_M2connector::probance_product_tier_price_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingProductTierPriceResource = $this->mappingProductTierPriceResource->create();
            $mappingProductTierPriceData = $this->getRequest()->getParam('mapping_product_tier_price_container');
            $mappingProductTierPriceResource->deleteMapping();

            if (is_array($mappingProductTierPriceData) && !empty($mappingProductTierPriceData)) {
                foreach ($mappingProductTierPriceData as $mappingProductTierPriceDatum) {
                    $model = $this->mappingProductTierPriceFactory->create();
                    unset($mappingProductTierPriceDatum['row_id']);
                    $model->addData($mappingProductTierPriceDatum);
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Product tier price mapping have been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/scope/stores');
    }
}
