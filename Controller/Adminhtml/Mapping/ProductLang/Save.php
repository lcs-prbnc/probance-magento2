<?php

namespace Probance\M2connector\Controller\Adminhtml\Mapping\ProductLang;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Probance\M2connector\Model\MappingProductLangFactory;
use Probance\M2connector\Model\ResourceModel\MappingProductLangFactory as MappingProductLangResourceFactory;

class Save extends Action
{
    /**
     * @var MappingProductLangFactory
     */
    protected $mappingProductLangFactory;

    /**
     * @var MappingProductLangResourceFactory
     */
    protected $mappingProductLangResource;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param MappingProductLangFactory $mappingProductLangFactory
     * @param MappingProductLangResourceFactory $mappingProductLangResource
     */
    public function __construct(
        Action\Context $context,
        MappingProductLangFactory $mappingProductLangFactory,
        MappingProductLangResourceFactory $mappingProductLangResource
    )
    {
        parent::__construct($context);
        $this->mappingProductLangFactory = $mappingProductLangFactory;
        $this->mappingProductLangResource = $mappingProductLangResource;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Probance_M2connector::probance_product_lang_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingProductLangResource = $this->mappingProductLangResource->create();
            $mappingProductLangData = $this->getRequest()->getParam('mapping_product_lang_container');
            $mappingProductLangResource->deleteMapping();

            if (is_array($mappingProductLangData) && !empty($mappingProductLangData)) {
                foreach ($mappingProductLangData as $mappingProductLangDatum) {
                    $model = $this->mappingProductLangFactory->create();
                    unset($mappingProductLangDatum['row_id']);
                    $model->addData($mappingProductLangDatum);
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Product lang mapping have been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/scope/stores');
    }
}
