<?php

namespace Probance\M2connector\Controller\Adminhtml\Mapping\Customer;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Probance\M2connector\Model\MappingCustomerFactory;
use Probance\M2connector\Model\ResourceModel\MappingCustomerFactory as MappingCustomerResourceFactory;

class Save extends Action
{
    /**
     * @var MappingCustomerFactory
     */
    protected $mappingCustomerFactory;

    /**
     * @var MappingCustomerResourceFactory
     */
    protected $mappingCustomerResource;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param MappingCustomerFactory $mappingCustomerFactory
     * @param MappingCustomerResourceFactory $mappingCustomerResource
     */
    public function __construct(
        Action\Context $context,
        MappingCustomerFactory $mappingCustomerFactory,
        MappingCustomerResourceFactory $mappingCustomerResource
    )
    {
        parent::__construct($context);
        $this->mappingCustomerFactory = $mappingCustomerFactory;
        $this->mappingCustomerResource = $mappingCustomerResource;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Probance_M2connector::probance_customer_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingCustomerResource = $this->mappingCustomerResource->create();
            $mappingCustomerData = $this->getRequest()->getParam('mapping_customer_container');
            $mappingCustomerResource->deleteMapping();

            if (is_array($mappingCustomerData) && !empty($mappingCustomerData)) {
                foreach ($mappingCustomerData as $mappingCustomerDatum) {
                    $model = $this->mappingCustomerFactory->create();
                    unset($mappingCustomerDatum['row_id']);
                    $model->addData($mappingCustomerDatum);
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Customer mapping have been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/scope/stores');
    }
}
