<?php

namespace Probance\M2connector\Controller\Adminhtml\Mapping\ArticleTierPrice;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Probance\M2connector\Model\MappingArticleTierPriceFactory;
use Probance\M2connector\Model\ResourceModel\MappingArticleTierPriceFactory as MappingArticleTierPriceResourceFactory;

class Save extends Action
{
    /**
     * @var MappingArticleTierPriceFactory
     */
    protected $mappingArticleTierPriceFactory;

    /**
     * @var MappingArticleTierPriceResourceFactory
     */
    protected $mappingArticleTierPriceResource;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param MappingArticleTierPriceFactory $mappingArticleTierPriceFactory
     * @param MappingArticleTierPriceResourceFactory $mappingArticleTierPriceResource
     */
    public function __construct(
        Action\Context $context,
        MappingArticleTierPriceFactory $mappingArticleTierPriceFactory,
        MappingArticleTierPriceResourceFactory $mappingArticleTierPriceResource
    )
    {
        parent::__construct($context);
        $this->mappingArticleTierPriceFactory = $mappingArticleTierPriceFactory;
        $this->mappingArticleTierPriceResource = $mappingArticleTierPriceResource;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Probance_M2connector::probance_article_tier_price_mapping');
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $mappingArticleTierPriceResource = $this->mappingArticleTierPriceResource->create();
            $mappingArticleTierPriceData = $this->getRequest()->getParam('mapping_article_tier_price_container');
            $mappingArticleTierPriceResource->deleteMapping();

            if (is_array($mappingArticleTierPriceData) && !empty($mappingArticleTierPriceData)) {
                foreach ($mappingArticleTierPriceData as $mappingArticleTierPriceDatum) {
                    $model = $this->mappingArticleTierPriceFactory->create();
                    unset($mappingArticleTierPriceDatum['row_id']);
                    $model->addData($mappingArticleTierPriceDatum);
                    $model->save();
                }
            }
            $this->messageManager->addSuccessMessage(__('Article tier price mapping have been saved successfully'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/scope/stores');
    }
}
