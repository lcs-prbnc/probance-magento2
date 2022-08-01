<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Model\ResourceModel\Iterator;
use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\LogFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as SubscriberCollectionFactory;
use Probance\M2connector\Model\ResourceModel\MappingCustomer\CollectionFactory as CustomerMappingCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;
use Magento\Newsletter\Model\Subscriber;
use Probance\M2connector\Model\Flow\Formater\CustomerFormater;
use Probance\M2connector\Model\Flow\Formater\SubscriberFormater;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Psr\Log\LoggerInterface;

class Customer extends AbstractFlow
{
    /**
     * Suffix use for filename defined configuration path
     */
    const EXPORT_CONF_FILENAME_SUFFIX = '';

    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'customer';

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var SubscriberCollectionFactory
     */
    protected $subscriberCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    
    /**
     * @var CustomerGroupRepository
     */
    protected $customerGroupRepository;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    /**
     * @var CustomerFormater
     */
    protected $customerFormater;

    /**
     * @var SubscriberFormater
     */
    protected $subscriberFormater;

    /**
     * @var TypeFactory
     */
    protected $typeFactory;

    /**
     * Customer constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     * @param LoggerInterface $logger

     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param SubscriberCollectionFactory $subscriberCollectionFactory
     * @param CustomerMappingCollectionFactory $customerMappingCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerGroupRepository $customerGroupRepository
     * @param Subscriber $subscriber
     * @param CustomerFormater $customerFormater
     * @param SubscriberFormater $subscriberFormater
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        Iterator $iterator,
        LogFactory $logFactory,
        LogRepositoryInterface $logRepository,
        LoggerInterface $logger,

        CustomerCollectionFactory $customerCollectionFactory,
        SubscriberCollectionFactory $subscriberCollectionFactory,
        CustomerMappingCollectionFactory $customerMappingCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerGroupRepository $customerGroupRepository,
        Subscriber $subscriber,
        CustomerFormater $customerFormater,
        SubscriberFormater $subscriberFormater,
        TypeFactory $typeFactory
    )
    {
        $this->flowMappingCollectionFactory = $customerMappingCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->subscriber = $subscriber;
        $this->customerFormater = $customerFormater;
        $this->subscriberFormater = $subscriberFormater;
        $this->typeFactory = $typeFactory;

        parent::__construct(
            $probanceHelper,
            $directoryList,
            $file,
            $ftp,
            $iterator,
            $logFactory,
            $logRepository,
            $logger
        );
    }

    /**
     * Customer callback
     *
     * @param $args
     */
    public function customerCallback($args)
    {
        try {
            $customer = $this->customerRepository->getById($args['row']['entity_id']);
        } catch (NoSuchEntityException $entityException) {
            return;
        } catch (\Exception $e) {
            return;
        }

        try {
            $this->customerFormater->setCustomerGroupRepository($this->customerGroupRepository);
            $this->customerFormater->setHelper($this->probanceHelper);
            
            foreach ($this->mapping['items'] as $mappingItem) {
                $key = $mappingItem['magento_attribute'];
                $dataKey = $key . '-' . $mappingItem['position'];
                $method = 'get' . $this->customerFormater->convertToCamelCase($key);

                $data[$dataKey] = '';

                if (!empty($mappingItem['user_value'])) {
                    $data[$dataKey] = $mappingItem['user_value'];
                    continue;
                }

                if (method_exists($this->customerFormater, $method)) {
                    $data[$dataKey] = $this->customerFormater->$method($customer);
                } else if (method_exists($customer, $method)) {
                    $data[$dataKey] = $customer->$method();
                } else {
                    $data[$dataKey] = $customer->getCustomAttribute($key);
                }

                $escaper = [
                    '~'.$this->probanceHelper->getFlowFormatValue('enclosure').'~'
                    => $this->probanceHelper->getFlowFormatValue('escape').$this->probanceHelper->getFlowFormatValue('enclosure')
                ];
                $data[$dataKey] = $this->typeFactory
                    ->getInstance($mappingItem['field_type'])
                    ->render($data[$dataKey], $mappingItem['field_limit'], $escaper);
            }

            $this->file->filePutCsv(
                $this->csv,
                $data,
                $this->probanceHelper->getFlowFormatValue('field_separator'),
                $this->probanceHelper->getFlowFormatValue('enclosure')
            );

            if ($this->progressBar) {
                $this->progressBar->setMessage('Processing: #' . $customer->getId(), 'status');
                $this->progressBar->advance();
            }

        } catch (\Exception $e) {
            $this->errors[] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
        }
        unset($customer);
    }

    /**
     * Subscriber callback
     *
     * @param array $args
     */
    public function subscriberCallback($args)
    {
        try {
            $subscriber = $this->subscriber->load($args['row']['subscriber_id']);
        } catch (\Exception $e) {
            return;
        }

        try {
            foreach ($this->mapping['items'] as $mappingItem) {
                $key = $mappingItem['magento_attribute'];
                $dataKey = $key . '-' . $mappingItem['position'];
                $method = 'get' . $this->subscriberFormater->convertToCamelCase($key);

                $data[$dataKey] = '';

                if (!empty($mappingItem['user_value'])) {
                    $data[$dataKey] = $mappingItem['user_value'];
                    continue;
                }

                if (method_exists($this->subscriberFormater, $method)) {
                    $data[$dataKey] = $this->subscriberFormater->$method($subscriber);
                } else if (method_exists($subscriber, $method)) {
                    $data[$dataKey] = $subscriber->$method();
                } else {
                    $data[$dataKey] ='';
                }

                $escaper = [
                    '~'.$this->probanceHelper->getFlowFormatValue('enclosure').'~'
                    => $this->probanceHelper->getFlowFormatValue('escape').$this->probanceHelper->getFlowFormatValue('enclosure')
                ];
                $data[$dataKey] = $this->typeFactory
                    ->getInstance($mappingItem['field_type'])
                    ->render($data[$dataKey], $mappingItem['field_limit'], $escaper);
            }

            $this->file->filePutCsv(
                $this->csv,
                $data,
                $this->probanceHelper->getFlowFormatValue('field_separator'),
                $this->probanceHelper->getFlowFormatValue('enclosure')
            );

            if ($this->progressBar) {
                $this->progressBar->setMessage('Processing: #' . $subscriber->getId(), 'status');
                $this->progressBar->advance();
            }

        } catch (\Exception $e) {
            $this->errors[] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
        }
    }

    /**
     * @return array
     */
    public function getArrayCollection()
    {
        $customerCollection = $this->customerCollectionFactory->create();
        $subscriberCollection = $this->subscriberCollectionFactory->create();

        if (isset($this->range['from']) && isset($this->range['to'])) {
            $customerCollection
                ->addFieldToFilter('updated_at', ['from' => $this->range['from']])
                ->addFieldToFilter('updated_at', ['to' => $this->range['to']]);

            $subscriberCollection
                ->addFieldToFilter('change_status_at', ['from' => $this->range['from']])
                ->addFieldToFilter('change_status_at', ['to' => $this->range['to']]);
        }

        return [
            [
                'object' => $customerCollection,
                'callback' => 'customerCallback'
            ],
            [
                'object' => $subscriberCollection,
                'callback' => 'subscriberCallback',
            ],
        ];
    }
}
