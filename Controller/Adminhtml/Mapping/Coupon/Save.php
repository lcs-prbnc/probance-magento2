<?php

namespace Probance\M2connector\Controller\Adminhtml\Mapping\Coupon;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Probance\M2connector\Model\MappingCouponFactory;
use Probance\M2connector\Model\ResourceModel\MappingCouponFactory as MappingCouponResourceFactory;

class Save extends Action
{
    /**
     * @var MappingCouponFactory
     */
    protected $mappingCouponFactory;

    /**
     * @var MappingCouponResourceFactory
     */
    protected $mappingCouponResource;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param MappingCouponFactory $mappingCouponFactory
     * @param MappingCouponResourceFactory $mappingCouponResource
     */
    public function __construct(
        Action\Context $context,
        MappingCouponFactory $mappingCouponFactory,
        MappingCouponResourceFactory $mappingCouponResource
    )
    {
        parent::__construct($context);
        $this->mappingCouponFactory = $mappingCouponFactory;
        $this->mappingCouponResource = $mappingCouponResource;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Probance_M2connector::probance_coupon_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingCouponResource = $this->mappingCouponResource->create();
            $mappingCouponData = $this->getRequest()->getParam('mapping_coupon_container');
            $mappingCouponResource->deleteMapping();

            if (is_array($mappingCouponData) && !empty($mappingCouponData)) {
                foreach ($mappingCouponData as $mappingCouponDatum) {
                    $model = $this->mappingCouponFactory->create();
                    unset($mappingCouponDatum['row_id']);
                    $model->addData($mappingCouponDatum);
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Coupon mapping have been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/scope/stores');
    }
}
