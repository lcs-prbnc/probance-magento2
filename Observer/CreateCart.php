<?php

namespace Walkwizus\Probance\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Walkwizus\Probance\Model\Api;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as ItemCollectionFactory;
use Walkwizus\Probance\Model\ResourceModel\MappingCart\CollectionFactory as CartMappingCollectionFactory;
use Walkwizus\Probance\Model\Flow\Formater\CartFormater;
use Walkwizus\Probance\Model\Config\Source\Sync\Mode;
use Walkwizus\Probance\Model\Flow\Type\Factory as TypeFactory;

class CreateCart implements ObserverInterface
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var ProbanceHelper
     */
    private $probanceHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ItemCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var CartMappingCollectionFactory
     */
    private $cartMappingCollectionFactory;

    /**
     * @var CartFormater
     */
    private $cartFormater;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Api $api,
        ProbanceHelper $probanceHelper,
        CheckoutSession $checkoutSession,
        ItemCollectionFactory $itemCollectionFactory,
        CartMappingCollectionFactory $cartMappingCollectionFactory,
        CartFormater $cartFormater,
        TypeFactory $typeFactory,
        LoggerInterface $logger
    )
    {
        $this->api = $api;
        $this->probanceHelper = $probanceHelper;
        $this->checkoutSession = $checkoutSession;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->cartMappingCollectionFactory = $cartMappingCollectionFactory;
        $this->cartFormater = $cartFormater;
        $this->typeFactory = $typeFactory;
        $this->logger = $logger;
    }

    /**
     * @param $quoteId
     * @return DataObject[]
     */
    protected function getQuoteItems($quoteId)
    {
        return $this->itemCollectionFactory
            ->create()
            ->addFieldToFilter('quote_id', $quoteId)
            ->getItems();
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (
            !$this->probanceHelper->getCartFlowValue('enabled') ||
            $this->probanceHelper->getCartFlowValue('sync_mode') != Mode::SYNC_MODE_REAL_TIME
        ) {
            return;
        }

        $quote = $this->checkoutSession->getQuote();

        if (!$quote->getId()) {
            return;
        }

        if (!$quote->getCustomerId()) {
            return;
        }

        $mapping = $this->cartMappingCollectionFactory
            ->create()
            ->setOrder('position', 'ASC')
            ->toArray();

        $productsRelation = [];

        foreach ($mapping['items'] as $mappingItem) {
            $magentoKey = $mappingItem['magento_attribute'];
            $probanceKey = $mappingItem['probance_attribute'];
            $method = 'get' . $this->cartFormater->convertToCamelCase($magentoKey);
            $data[$probanceKey] = '';

            if (!empty($mappingItem['user_value'])) {
                $data[$probanceKey] = $mappingItem['user_value'];
                continue;
            }

            if (method_exists($this->cartFormater, $method)) {
                $data[$probanceKey] = $this->cartFormater->$method($item);
            } else if (method_exists($item, $method)) {
                $data[$probanceKey] = $item->$method();
            }

            $data[$probanceKey] = $this->typeFactory
                ->getInstance($mappingItem['field_type'])
                ->render($data[$probanceKey], $mappingItem['field_limit']);
        }
    }
}