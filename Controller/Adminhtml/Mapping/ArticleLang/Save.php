<?php

namespace Probance\M2connector\Controller\Adminhtml\Mapping\ArticleLang;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Probance\M2connector\Model\MappingArticleLangFactory;
use Probance\M2connector\Model\ResourceModel\MappingArticleLangFactory as MappingArticleLangResourceFactory;

class Save extends Action
{
    /**
     * @var MappingArticleLangFactory
     */
    protected $mappingArticleLangFactory;

    /**
     * @var MappingArticleLangResourceFactory
     */
    protected $mappingArticleLangResource;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param MappingArticleLangFactory $mappingArticleLangFactory
     * @param MappingArticleLangResourceFactory $mappingArticleLangResource
     */
    public function __construct(
        Action\Context $context,
        MappingArticleLangFactory $mappingArticleLangFactory,
        MappingArticleLangResourceFactory $mappingArticleLangResource
    )
    {
        parent::__construct($context);
        $this->mappingArticleLangFactory = $mappingArticleLangFactory;
        $this->mappingArticleLangResource = $mappingArticleLangResource;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Probance_M2connector::probance_article_lang_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingArticleLangResource = $this->mappingArticleLangResource->create();
            $mappingArticleLangData = $this->getRequest()->getParam('mapping_article_lang_container');
            $mappingArticleLangResource->deleteMapping();

            if (is_array($mappingArticleLangData) && !empty($mappingArticleLangData)) {
                foreach ($mappingArticleLangData as $mappingArticleLangDatum) {
                    $model = $this->mappingArticleLangFactory->create();
                    unset($mappingArticleLangDatum['row_id']);
                    $model->addData($mappingArticleLangDatum);
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Article lang mapping have been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/scope/stores');
    }
}
