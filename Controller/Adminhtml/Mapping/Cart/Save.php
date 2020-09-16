<?php

namespace Walkwizus\Probance\Controller\Adminhtml\Mapping\Cart;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Walkwizus\Probance\Model\MappingCartFactory;

class Save extends Action
{
    /**
     * @var MappingCartFactory
     */
    protected $mappingCartFactory;

    /**
     * @var \Walkwizus\Probance\Model\ResourceModel\MappingCartFactory
     */
    protected $mappingCartResource;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param MappingCartFactory $mappingCartFactory
     * @param \Walkwizus\Probance\Model\ResourceModel\MappingCartFactory $mappingCartResource
     */
    public function __construct(
        Action\Context $context,
        MappingCartFactory $mappingCartFactory,
        \Walkwizus\Probance\Model\ResourceModel\MappingCartFactory $mappingCartResource
    )
    {
        parent::__construct($context);
        $this->mappingCartFactory = $mappingCartFactory;
        $this->mappingCartResource = $mappingCartResource;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Walkwizus_Probance::probance_cart_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingCartResource = $this->mappingCartResource->create();
            $mappingCartData = $this->getRequest()->getParam('mapping_cart_container');
            $mappingCartResource->deleteCartMapping();

            if (is_array($mappingCartData) && !empty($mappingCartData)) {
                foreach ($mappingCartData as $mappingCartDatum) {
                    $model = $this->mappingCartFactory->create();
                    unset($mappingCartDatum['row_id']);
                    $model->addData($mappingCartDatum);
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Cart mapping have been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/scope/stores');
    }
}