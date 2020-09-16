<?php

namespace Walkwizus\Probance\Controller\Adminhtml\Mapping\Article;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Walkwizus\Probance\Model\MappingArticleFactory;

class Save extends Action
{
    /**
     * @var MappingProductFactory
     */
    protected $mappingArticleFactory;

    /**
     * @var \Walkwizus\Probance\Model\ResourceModel\MappingArticleFactory
     */
    protected $mappingArticleResource;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param MappingArticleFactory $mappingArticleFactory
     * @param \Walkwizus\Probance\Model\ResourceModel\MappingArticleFactory $mappingArticleResource
     */
    public function __construct(
        Action\Context $context,
        MappingArticleFactory $mappingArticleFactory,
        \Walkwizus\Probance\Model\ResourceModel\MappingArticleFactory $mappingArticleResource
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
        return $this->_authorization->isAllowed('Walkwizus_Probance::probance_article_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingArticleResource = $this->mappingArticleResource->create();
            $mappingArticleData = $this->getRequest()->getParam('mapping_article_container');
            $mappingArticleResource->deleteArticleMapping();

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