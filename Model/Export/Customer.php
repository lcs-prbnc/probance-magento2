<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Probance\M2connector\Model\BatchIterator as Iterator;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as SubscriberCollectionFactory;
use Probance\M2connector\Model\ResourceModel\MappingCustomer\CollectionFactory as CustomerMappingCollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;
use Magento\Newsletter\Model\SubscriberFactory;
use Probance\M2connector\Model\Flow\Formater\CustomerFormater;
use Probance\M2connector\Model\Flow\Formater\SubscriberFormater;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;

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
     * @var CustomerFactory
     */
    protected $customerFactory;
    
    /**
     * @var CustomerGroupRepository
     */
    protected $customerGroupRepository;

    /**
     * @var Subscriber
     */
    protected $subscriberFactory;

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

     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param SubscriberCollectionFactory $subscriberCollectionFactory
     * @param CustomerMappingCollectionFactory $customerMappingCollectionFactory
     * @param CustomerFactory $customerFactory
     * @param CustomerGroupRepository $customerGroupRepository
     * @param Subscriber $subscriberFactory
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

        CustomerCollectionFactory $customerCollectionFactory,
        SubscriberCollectionFactory $subscriberCollectionFactory,
        CustomerMappingCollectionFactory $customerMappingCollectionFactory,
        CustomerFactory $customerFactory,
        CustomerGroupRepository $customerGroupRepository,
        SubscriberFactory $subscriberFactory,
        CustomerFormater $customerFormater,
        SubscriberFormater $subscriberFormater,
        TypeFactory $typeFactory
    )
    {
        $this->flowMappingCollectionFactory = $customerMappingCollectionFactory;
        $this->customerFactory = $customerFactory;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerFormater = $customerFormater;
        $this->subscriberFormater = $subscriberFormater;
        $this->typeFactory = $typeFactory;

        parent::__construct(
            $probanceHelper,
            $directoryList,
            $file,
            $ftp,
            $iterator
        );
    }

    /**
     * Customer callback
     *
     * @param $entity
     */
    public function customerCallback($entity)
    {
        try {
            $customerModel = $this->customerFactory->create()->load($entity->getId());
            $customer = $customerModel->getDataModel();
            if ($this->progressBar) {
                $this->progressBar->setMessage(__('Processing: #%1', $customer->getId()), 'status');
            }
        } catch (NoSuchEntityException $entityException) {
            return;
        } catch (\Exception $e) {
            return;
        }

        try {
            $this->customerFormater->setCustomerGroupRepository($this->customerGroupRepository);
           
            $data = []; 
            foreach ($this->mapping['items'] as $mappingItem) {
                $key = $mappingItem['magento_attribute'];
                $dataKey = $key . '-' . $mappingItem['position'];
                list($key, $subAttribute) = $this->getSubAttribute($key);
                $method = 'get' . $this->customerFormater->convertToCamelCase($key);

                $data[$dataKey] = '';

                if (!empty($mappingItem['user_value'])) {
                    $data[$dataKey] = $mappingItem['user_value'];
                    continue;
                }

                if (method_exists($this->customerFormater, $method)) {
                    if ($subAttribute) $data[$dataKey] = $this->customerFormater->$method($customer, $subAttribute);
                    else $data[$dataKey] = $this->customerFormater->$method($customer);
                } else if (method_exists($customer, $method)) {
                    $data[$dataKey] = $customer->$method();
                } else {
                    $customAttribute = $customer->getCustomAttribute($key);
                    if ($customAttribute) {
                        $data[$dataKey] = $this->customerFormater->formatValueWithRenderer($key, $customer);
                    }
                }

                $escaper = [
                    '~'.$this->probanceHelper->getFlowFormatValue('enclosure').'~'
                    => $this->probanceHelper->getFlowFormatValue('escape').$this->probanceHelper->getFlowFormatValue('enclosure')
                ];
                $data[$dataKey] = $this->typeFactory
                    ->getInstance($mappingItem['field_type'])
                    ->render($data[$dataKey], $mappingItem['field_limit'], $escaper);
            }

            @fputcsv(
                $this->csv,
                $this->probanceHelper->postProcessData($data),
                $this->probanceHelper->getFlowFormatValue('field_separator'),
                $this->probanceHelper->getFlowFormatValue('enclosure')
            );

            if ($this->progressBar) {
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
    public function subscriberCallback($entity)
    {
        try {
            $subscriber = $this->subscriberFactory->create()->load($entity->getId());
            if ($this->progressBar) {
                $this->progressBar->setMessage(__('Processing: #%1', $subscriber->getId()), 'status');
            }
        } catch (\Exception $e) {
            return;
        }

        try {
            $data = []; 
            foreach ($this->mapping['items'] as $mappingItem) {
                $key = $mappingItem['magento_attribute'];
                $dataKey = $key . '-' . $mappingItem['position'];
                list($key, $subAttribute) = $this->getSubAttribute($key);
                $method = 'get' . $this->subscriberFormater->convertToCamelCase($key);

                $data[$dataKey] = '';

                if (!empty($mappingItem['user_value'])) {
                    $data[$dataKey] = $mappingItem['user_value'];
                    continue;
                }

                if (method_exists($this->subscriberFormater, $method)) {
                    if ($subAttribute) $data[$dataKey] = $this->subscriberFormater->$method($subscriber, $subAttribute);
                    else $data[$dataKey] = $this->subscriberFormater->$method($subscriber);
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
            @fputcsv(
                $this->csv,
                $this->probanceHelper->postProcessData($data),
                $this->probanceHelper->getFlowFormatValue('field_separator'),
                $this->probanceHelper->getFlowFormatValue('enclosure')
            );

            if ($this->progressBar) {
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
     * @param $storeId
     * @return array
     */
    public function getArrayCollection($storeId)
    {
        $websiteId = $this->probanceHelper->getWebsiteId($storeId);

        $customerCollection = $this->customerCollectionFactory->create();
        $customerCollection->addAttributeToFilter([ 
            ['attribute'=>'website_id','eq'=>$websiteId], 
            ['attribute'=>'store_id','eq' => $storeId] 
        ]);

        $subscriberCollection = $this->subscriberCollectionFactory->create();
        $subscriberCollection->addFieldToFilter('store_id', $storeId);

        if (isset($this->range['from']) && isset($this->range['to'])) {
            $customerCollection
                ->addFieldToFilter('updated_at', ['from' => $this->range['from']])
                ->addFieldToFilter('updated_at', ['to' => $this->range['to']]);

            $subscriberCollection
                ->addFieldToFilter('change_status_at', ['from' => $this->range['from']])
                ->addFieldToFilter('change_status_at', ['to' => $this->range['to']]);
        }

        if ($this->entityId) {
            $customerCollection->addFieldToFilter($customerCollection->getResource()->getIdFieldName(), $this->entityId);
            $subscriberCollection->addFieldToFilter('customer_id', $this->entityId);
        }

        $currentPage = $this->checkForNextPage($customerCollection);
        // Get next page for customer
        $nextPageCustomer = $this->getNextPage();
        // Reset to current before check for subscriber
        $this->setNextPage($currentPage);
        $currentPage = $this->checkForNextPage($subscriberCollection);
        // Get next page for subscriber
        $nextPageSubscriber = $this->getNextPage();
        // Set next page if one collection get one
        $this->setNextPage($nextPageCustomer ?? $nextPageSubscriber);

        if ($this->progressBar) {
            $this->progressBar->setMessage(__('Treating page %1', $currentPage), 'warn');
        }

        if ($this->getNextPage() == 0) {
            $countCustomer = $countSubscriber = $this->getLimit();
        } else {
            $countCustomer = $customerCollection->getSize();
            $countSubscriber = $subscriberCollection->getSize();
        }

        return [
            [
                'object'        => $customerCollection,
                'count'         => $countCustomer,
                'callback'      => 'customerCallback'
            ],
            [
                'object'        => $subscriberCollection,
                'count'         => $countSubscriber,
                'callback'      => 'subscriberCallback',
            ],
        ];
    }
}
