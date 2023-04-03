<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Model\ResourceModel\Iterator;
use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\LogFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Probance\M2connector\Model\ResourceModel\MappingOrder\CollectionFactory as OrderMappingCollectionFactory;
use Probance\M2connector\Model\Flow\Formater\OrderFormater;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Psr\Log\LoggerInterface;

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
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     * @param LoggerInterface $logger

     * @param OrderCollectionFactory $orderCollectionFactory
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
        LogFactory $logFactory,
        LogRepositoryInterface $logRepository,
        LoggerInterface $logger,

        OrderCollectionFactory $orderCollectionFactory,
        OrderMappingCollectionFactory $orderMappingCollectionFactory,
        OrderFormater $orderFormater,
        TypeFactory $typeFactory
    )
    {
        $this->flowMappingCollectionFactory = $orderMappingCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderFormater = $orderFormater;
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
     * Callback Order
     *
     * @param $args
     */
    public function orderCallback($args)
    {
        try {
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('entity_id', $args['row']['entity_id'])->setPage(1,1);
            $order = $orderCollection->getFirstItem();
            $orderCollection = null;
            if (!$order || !$order->getIncrementId()) {
                throw new \Exception('Order '.$args['row']['entity_id'].' not found'); 
            }
            if ($this->progressBar) {
                $this->progressBar->setMessage('Processing: #' . $order->getIncrementId(), 'status');
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
                    $parent = $order->getItemById($item->getParentItemId());
                    if ($parent) $productsRelation[$parent->getProductId()] = $item->getProductId();
                    $parent=null;
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
                    $method = 'get' . $this->orderFormater->convertToCamelCase($key);

                    $data[$dataKey] = '';

                    if (!empty($mappingItem['user_value'])) {
                        $data[$dataKey] = $mappingItem['user_value'];
                        continue;
                    }

                    if (method_exists($this->orderFormater, $method)) {
                        $data[$dataKey] = $this->orderFormater->$method($item);
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
                    $data,
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
     * @return array
     */
    public function getArrayCollection()
    {
        $statuses = explode(',', $this->probanceHelper->getGivenFlowValue($this->flow, 'status'));
        $orderCollection = $this->orderCollectionFactory->create();

        $startDate = $this->probanceHelper->getGivenFlowValue($this->flow, 'startdate');
        if ($startDate) {
            $orderCollection->addFieldToFilter('created_at', ['from' => $startDate]);
        }

        if (isset($this->range['from']) && isset($this->range['to'])) {
            $orderCollection
                ->addFieldToFilter('updated_at', ['from' => $this->range['from']])
                ->addFieldToFilter('updated_at', ['to' => $this->range['to']]);
        }

        $orderCollection->addFieldToFilter('status', ['in' => $statuses]);
        $orderCollection->addItemCountExpr();

        $sumCollection = clone $orderCollection;
        $sumCollection->addFieldToSelect('store_id');
        $sumCollection->addExpressionFieldToSelect('mytotal', 'SUM({{total_item_count}})', 'total_item_count')->getSelect()->group('store_id');
        $count = 0;
        foreach ($sumCollection as $line) {
            $count += $line->getData('mytotal');
        }
        $sumCollection = null;

        return [
            [
                'object' => $orderCollection,
                'count' => $count,
                'callback' => 'orderCallback',
            ],
        ];
    }
}
