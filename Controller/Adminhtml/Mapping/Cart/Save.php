<?php

namespace Probance\M2connector\Controller\Adminhtml\Mapping\Cart;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Probance\M2connector\Model\MappingCartFactory;
use Probance\M2connector\Model\ResourceModel\MappingCartFactory as MappingCartResourceFactory;

class Save extends Action
{
    /**
     * @var MappingCartFactory
     */
    protected $mappingCartFactory;

    /**
     * @var MappingCartResourceFactory
     */
    protected $mappingCartResource;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param MappingCartFactory $mappingCartFactory
     * @param MappingCartResourceFactory $mappingCartResource
     */
    public function __construct(
        Action\Context $context,
        MappingCartFactory $mappingCartFactory,
        MappingCartResourceFactory $mappingCartResource
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
        return $this->_authorization->isAllowed('Probance_M2connector::probance_cart_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingCartResource = $this->mappingCartResource->create();
            $mappingCartData = $this->getRequest()->getParam('mapping_cart_container');
            $mappingCartResource->deleteMapping();

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
