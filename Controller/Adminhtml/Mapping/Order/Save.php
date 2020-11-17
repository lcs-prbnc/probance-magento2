<?php

namespace Probance\M2connector\Controller\Adminhtml\Mapping\Order;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Probance\M2connector\Model\MappingOrderFactory;

class Save extends Action
{
    /**
     * @var MappingOrderFactory
     */
    protected $mappingOrderFactory;

    /**
     * @var \Probance\M2connector\Model\ResourceModel\MappingOrderFactory
     */
    protected $mappingOrderResource;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param MappingOrderFactory $mappingOrderFactory
     * @param \Probance\M2connector\Model\ResourceModel\MappingOrderFactory $mappingOrderResource
     */
    public function __construct(
        Action\Context $context,
        MappingOrderFactory $mappingOrderFactory,
        \Probance\M2connector\Model\ResourceModel\MappingOrderFactory $mappingOrderResource
    )
    {
        parent::__construct($context);
        $this->mappingOrderFactory = $mappingOrderFactory;
        $this->mappingOrderResource = $mappingOrderResource;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Probance_M2connector::probance_order_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingOrderResource = $this->mappingOrderResource->create();
            $mappingOrderData = $this->getRequest()->getParam('mapping_order_container');
            $mappingOrderResource->deleteOrderMapping();

            if (is_array($mappingOrderData) && !empty($mappingOrderData)) {
                foreach ($mappingOrderData as $mappingOrderDatum) {
                    $model = $this->mappingOrderFactory->create();
                    unset($mappingOrderDatum['row_id']);
                    $model->addData($mappingOrderDatum);
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Order mapping have been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/scope/stores');
    }
}