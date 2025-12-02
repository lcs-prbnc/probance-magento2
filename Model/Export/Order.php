<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Probance\M2connector\Model\BatchIterator as Iterator;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\OrderFactory as OrderFactory;
use Probance\M2connector\Model\ResourceModel\MappingOrder\CollectionFactory as OrderMappingCollectionFactory;
use Probance\M2connector\Model\Flow\Formater\OrderFormater;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;

class Order extends AbstractFlow
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
    protected $flow = 'order';

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderMappingCollectionFactory
     */
    protected $orderMappingCollectionFactory;

    /**
     * @var OrderFormater
     */
    protected $orderFormater;

    /**
     * @var TypeFactory
     */
    protected $typeFactory;

    /**
     * Order constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator

     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderFactory $orderFactory
     * @param OrderMappingCollectionFactory $orderMappingCollectionFactory
     * @param OrderFormater $orderFormater
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        Iterator $iterator,

        OrderMappingCollectionFactory $orderMappingCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        OrderFactory $orderFactory,
        OrderFormater $orderFormater,
        TypeFactory $typeFactory
    )
    {
        $this->flowMappingCollectionFactory = $orderMappingCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->orderFormater = $orderFormater;
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
     * Callback Order
     *
     * @param $entity
     */
    public function orderCallback($entity)
    {
        try {
            $order = $this->orderFactory->create()->load($entity->getId());
            if (!$order || !$order->getIncrementId()) {
                throw new \Exception('Order '.$entity->getId().' not found'); 
            }
            if ($this->progressBar) {
                $this->progressBar->setMessage(__('Processing: #%1', $order->getIncrementId()), 'status');
            }
        } catch (\Exception $e) {
            $this->errors[] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
            $order = null;
            return false;
        }

        try {
            $allItems = $order->getAllItems();
            $productsRelation = [];
            foreach ($allItems as $item) {
                if ($item->getParentItemId()) {
                    $productsRelation[$item->getParentItemId()] = $item->getProductId();
                }
            }
            $this->orderFormater->setProductRelation($productsRelation);
            $this->orderFormater->setOrder($order);

            foreach ($allItems as $item) {
                if ($item->getParentItemId()) continue;
                $data = [];
                foreach ($this->mapping['items'] as $mappingItem) {
                    $key = $mappingItem['magento_attribute'];
                    $dataKey = $key . '-' . $mappingItem['position'];
                    list($key, $subAttribute) = $this->getSubAttribute($key);
                    $method = 'get' . $this->orderFormater->convertToCamelCase($key);

                    $data[$dataKey] = '';

                    if (!empty($mappingItem['user_value'])) {
                        $data[$dataKey] = $mappingItem['user_value'];
                        continue;
                    }

                    if (method_exists($this->orderFormater, $method)) {
                        if ($subAttribute) $data[$dataKey] = $this->orderFormater->$method($item, $subAttribute);
                        else $data[$dataKey] = $this->orderFormater->$method($item);
                    } else if (method_exists($item, $method)) {
                        $data[$dataKey] = $item->$method();
                    } else if (method_exists($order, $method)) {
                        $data[$dataKey] = $order->$method();
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
                    $this->probanceHelper->postProcessData($data),
                    $this->probanceHelper->getFlowFormatValue('field_separator'),
                    $this->probanceHelper->getFlowFormatValue('enclosure')
                );

                if ($this->progressBar) {
                    $this->progressBar->advance();
                }
            }
            $allItems = null;
            $order = null;
            $productsRelation = null;
            $data = null;
        } catch (\Exception $e) {
            $this->errors[] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
            $allItems = null;
            $order = null;
            $productsRelation = null;
            $data = null;
            return false;
        }
    }

    /**
     * @param $storeId
     * @return array
     */
    public function getArrayCollection($storeId)
    {
        $statuses = array_map('trim', explode(',', $this->probanceHelper->getGivenFlowValue($this->flow, 'status')));
        $orderCollection = $this->orderCollectionFactory->create();

        $orderCollection->addFieldToFilter('store_id', $storeId);

        $startDate = $this->probanceHelper->getGivenFlowValue($this->flow, 'startdate');
        if ($startDate) {
            $orderCollection->addFieldToFilter('created_at', ['from' => $startDate]);
        }

        if (isset($this->range['from']) && isset($this->range['to'])) {
            $orderCollection
                ->addFieldToFilter('updated_at', ['from' => $this->range['from']])
                ->addFieldToFilter('updated_at', ['to' => $this->range['to']]);
        }

        if ($this->entityId) {
            $orderCollection->addFieldToFilter($orderCollection->getResource()->getIdFieldName(), $this->entityId);
        }
            
        $orderCollection->addFieldToFilter('status', ['in' => $statuses]);
        $orderCollection->addItemCountExpr();
        $orderCollection->setOrder($orderCollection->getResource()->getIdFieldName(), 'asc');

        $currentPage = $this->checkForNextPage($orderCollection);

        if ($this->getNextPage() == 0) $count = $this->getLimit();
        else {
            $sumCollection = clone $orderCollection;
            $sumCollection->addItemCountExpr();
            $count = 0;
            foreach ($sumCollection as $line) {
                $count += $line->getData('total_item_count');
            }
            $sumCollection = null;
        }

        if ($this->progressBar) {
            $this->progressBar->setMessage(__('Treating page %1, with %2 order lines', $currentPage, $count), 'warn');
        }

        return [
            [
                'object'    => $orderCollection,
                'count'     => $count,
                'callback'  => 'orderCallback',
            ],
        ];
    }
}
