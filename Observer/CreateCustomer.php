<?php

namespace Probance\M2connector\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Probance\M2connector\Model\Api;
use Probance\M2connector\Model\Config\Source\Sync\Mode;
use Probance\M2connector\Model\Flow\Formater\CustomerFormater;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\ResourceModel\MappingCustomer\CollectionFactory as CustomerMappingCollectionFactory;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;

class CreateCustomer implements ObserverInterface
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerFormater
     */
    private $customerFormater;

    /**
     * @var CustomerMappingCollectionFactory
     */
    private $customerMappingCollectionFactory;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var ProbanceHelper
     */
    private $probanceHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CreateCustomer constructor.
     *
     * @param Api $api
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerFormater $customerFormater
     * @param CustomerMappingCollectionFactory $customerMappingCollectionFactory
     * @param TypeFactory $typeFactory
     * @param ProbanceHelper $probanceHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Api $api,
        CustomerRepositoryInterface $customerRepository,
        CustomerFormater $customerFormater,
        CustomerMappingCollectionFactory $customerMappingCollectionFactory,
        TypeFactory $typeFactory,
        ProbanceHelper $probanceHelper,
        LoggerInterface $logger
    )
    {
        $this->api = $api;
        $this->customerRepository = $customerRepository;
        $this->customerFormater = $customerFormater;
        $this->customerMappingCollectionFactory = $customerMappingCollectionFactory;
        $this->typeFactory = $typeFactory;
        $this->probanceHelper = $probanceHelper;
        $this->logger = $logger;
    }

    /**
     * Create customer
     *
     * @param Observer $observer
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (
            !$this->probanceHelper->getCustomerFlowValue('enabled') ||
            $this->probanceHelper->getCustomerFlowValue('sync_mode') != Mode::SYNC_MODE_REAL_TIME
        ) {
            return;
        }

        $event = $observer->getEvent();
        $customerData = $event->getCustomerDataObject();
        $customer = $this->customerRepository->getById($customerData->getId());

        $mapping = $this->customerMappingCollectionFactory
            ->create()
            ->setOrder('position', 'ASC')
            ->toArray();

        $data = [];
        foreach ($mapping['items'] as $mappingItem) {
            $magentoKey = $mappingItem['magento_attribute'];
            $probanceKey = $mappingItem['probance_attribute'];

            $data[$probanceKey] = '';
            $method = 'get' . $this->customerFormater->convertToCamelCase($magentoKey);

            if (!empty($mappingItem['user_value'])) {
                $data[$probanceKey] = $mappingItem['user_value'];
                continue;
            }

            if (method_exists($this->customerFormater, $method)) {
                $data[$probanceKey] = $this->customerFormater->$method($customer);
            } else if (method_exists($customer, $method)) {
                $data[$probanceKey] = $customer->$method();
            } else {
                $customAttribute = $customer->getCustomAttribute($magentoKey);
                if ($customAttribute) {
                    $data[$probanceKey] = $this->formatValueWithRenderer($magentoKey, $customer);
                }
            }

            $data[$probanceKey] = $this->typeFactory
                ->getInstance($mappingItem['field_type'])
                ->render($data[$probanceKey], $mappingItem['field_limit']);
        }

        $this->api->call($this->probanceHelper->getCustomerFlowValue('filename'), $data);
    }
}