<?php

declare(strict_types=1);

namespace Probance\M2connector\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Helper\Page as CmsPageHelper;

use Probance\M2connector\Helper\Data as ProbanceHelper;

class Recovery extends \Magento\Framework\App\Action\Action
{
    /** @var ProbanceHelper */
    protected $helper;

    /** @var ResultFactory */
    protected $resultFactory;

    /** @var CartHelper */ 
    protected $cartHelper;

    /** @var GetPageByIdentifierInterface */
    protected $pageByIdentifier;

    /** @var CmsPageHelper */
    protected $cmsPageHelper;

    /**
     * Index constructor.
     * @param Context $context
     * @param ProbanceHelper $helper
     * @param ResultFactory $resultFactory
     * @param CartHelper $cartHelper
     * @param GetPageByIdentifierInterface $pageByIdentifier
     * @param CmsPageHelper $cmsPageHelper
     */
    public function __construct(
        Context $context,
        ProbanceHelper $helper,
        ResultFactory $resultFactory,
        CartHelper $cartHelper,
        GetPageByIdentifierInterface $pageByIdentifier,
        CmsPageHelper $cmsPageHelper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->resultFactory = $resultFactory;
        $this->cartHelper = $cartHelper;
        $this->pageByIdentifier = $pageByIdentifier;
        $this->cmsPageHelper = $cmsPageHelper;
    }

    /**
     * @return ResultInterface
     */
    public function execute() 
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect = $this->helper->getRecoveryCartRedirect();
        if ($redirect) {
            // forward to cart
            $resultRedirect->setUrl($this->cartHelper->getCartUrl());
        } else {
            $cmsPage = $this->helper->getRecoveryCartPage();
            if ($cmsPage) {
                // forward to cms
                $url = $this->cmsPageHelper->getPageUrl($cmsPage->getId());
                $resultRedirect->setUrl($url);
            } else {
                // forward to cart
                $resultRedirect->setUrl($this->cartHelper->getCartUrl());
            }
        }
        return $resultRedirect;
    }
}
