<?php

namespace Probance\M2connector\Controller\Adminhtml\Mapping\Article;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Probance\M2connector\Model\MappingArticleFactory;
use Probance\M2connector\Model\ResourceModel\MappingArticleFactory as MappingArticleResourceFactory;

class Save extends Action
{
    /**
     * @var MappingProductFactory
     */
    protected $mappingArticleFactory;

    /**
     * @var MappingArticleResourceFactory
     */
    protected $mappingArticleResource;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param MappingArticleFactory $mappingArticleFactory
     * @param MappingArticleResourceFactory $mappingArticleResource
     */
    public function __construct(
        Action\Context $context,
        MappingArticleFactory $mappingArticleFactory,
        MappingArticleResourceFactory $mappingArticleResource
    )
    {
        parent::__construct($context);
        $this->mappingArticleFactory = $mappingArticleFactory;
        $this->mappingArticleResource = $mappingArticleResource;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Probance_M2connector::probance_article_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingArticleResource = $this->mappingArticleResource->create();
            $mappingArticleData = $this->getRequest()->getParam('mapping_article_container');
            $mappingArticleResource->deleteMapping();

            if (is_array($mappingArticleData) && !empty($mappingArticleData)) {
                foreach ($mappingArticleData as $mappingArticleDatum) {
                    $model = $this->mappingArticleFactory->create();
                    unset($mappingArticleDatum['row_id']);
                    $model->addData($mappingArticleDatum);
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Article mapping have been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/scope/stores');
    }
}
