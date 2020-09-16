<?php

namespace Walkwizus\Probance\Controller\Adminhtml\Mapping\Product;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Walkwizus\Probance\Model\MappingProductFactory;

class Save extends Action
{
    /**
     * @var MappingProductFactory
     */
    protected $mappingProductFactory;

    /**
     * @var \Walkwizus\Probance\Model\ResourceModel\MappingProductFactory
     */
    protected $mappingProductResource;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param MappingProductFactory $mappingProductFactory
     * @param \Walkwizus\Probance\Model\ResourceModel\MappingProductFactory $mappingProductResource
     */
    public function __construct(
        Action\Context $context,
        MappingProductFactory $mappingProductFactory,
        \Walkwizus\Probance\Model\ResourceModel\MappingProductFactory $mappingProductResource
    )
    {
        parent::__construct($context);
        $this->mappingProductFactory = $mappingProductFactory;
        $this->mappingProductResource = $mappingProductResource;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Walkwizus_Probance::probance_product_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingProductResource = $this->mappingProductResource->create();
            $mappingProductData = $this->getRequest()->getParam('mapping_product_container');
            $mappingProductResource->deleteProductMapping();

            if (is_array($mappingProductData) && !empty($mappingProductData)) {
                foreach ($mappingProductData as $mappingProductDatum) {
                    $model = $this->mappingProductFactory->create();
                    unset($mappingProductDatum['row_id']);
                    $model->addData($mappingProductDatum);
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Product mapping have been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/scope/stores');
    }
}