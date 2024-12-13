<?php

declare(strict_types=1);

namespace Probance\M2connector\Controller\Visit;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Session\Proxy as CustomerSession;
use Magento\Catalog\Helper\Data as CatalogHelper;

class Data extends \Magento\Framework\App\Action\Action
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var ResultFactory */
    protected $resultFactory;

    /** @var CustomerSession */
    protected $customerSession;

    /** @var CatalogHelper */
    protected $catalogHelper;

    /**
     * Index constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param ResultFactory $resultFactory
     * @param CustomerSession $customerSession
     * @param CatalogHelper $catalogHelper
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        ResultFactory $resultFactory,
        CustomerSession $customerSession,
        CatalogHelper $catalogHelper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->resultFactory = $resultFactory;
        $this->customerSession = $customerSession;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @return ResultInterface
     */
    public function execute() 
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {

            $customerEmail = $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomer()->getEmail() : '';
            $product = $this->catalogHelper->getProduct();
            $productId = ($product ? $product->getId() : '');
            $data = [
                'status'        => 'success',
                'data' => [
                    'customerEmail' => $customerEmail,
                    'productId'     => $productId,
                ]
            ];
            $result->setData($data);

        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result->setData([
                'status'        => 'error',
                'errorMessage'  => $e->getMessage()
            ]);
            $result->setStatusHeader(500);
        }

        return $result;
    }
}
