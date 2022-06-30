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
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order\ItemRepository;
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
    private $orderCollectionFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * @var OrderMappingCollectionFactory
     */
    private $orderMappingCollectionFactory;

    /**
     * @var OrderFormater
     */
    private $orderFormater;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

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
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderRepository $orderRepository
     * @param ItemRepository $itemRepository
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
        OrderCollectionFactory $orderCollectionFactory,
        OrderRepository $orderRepository,
        ItemRepository $itemRepository,
        OrderMappingCollectionFactory $orderMappingCollectionFactory,
        OrderFormater $orderFormater,
        TypeFactory $typeFactory
    )
    {
        $this->flowMappingCollectionFactory = $orderMappingCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->itemRepository = $itemRepository;
        $this->orderFormater = $orderFormater;
        $this->typeFactory = $typeFactory;

        parent::__construct(
            $probanceHelper,
            $directoryList,
            $file,
            $ftp,
            $iterator,
            $logFactory,
            $logRepository
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
            $order = $this->orderRepository->get($args['row']['entity_id']);
        } catch (\Exception $e) {
            return;
        }

        try {
            $allItems = $order->getAllItems();
            $productsRelation = [];

            foreach ($allItems as $allItem) {
                if ($allItem->getParentItemId()) {
                    $parent = $this->itemRepository->get($allItem->getParentItemId());
                    $productsRelation[$parent->getProductId()] = $allItem->getProductId();
                }
            }

            $this->orderFormater->setProductRelation($productsRelation);
            $this->orderFormater->setOrder($order);
            $items = $order->getAllVisibleItems();

            foreach ($items as $item) {
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
                    }

                    $data[$dataKey] = $this->typeFactory
                        ->getInstance($mappingItem['field_type'])
                        ->render($data[$dataKey], $mappingItem['field_limit']);
                }

                $this->file->filePutCsv(
                    $this->csv,
                    $data,
                    $this->probanceHelper->getFlowFormatValue('field_separator'),
                    $this->probanceHelper->getFlowFormatValue('enclosure')
                );

                if ($this->progressBar) {
                    $this->progressBar->setMessage('Processing: #' . $order->getIncrementId(), 'status');
                    $this->progressBar->advance();
                }
            }
        } catch (\Exception $e) {

        }
    }

    /**
     * @return array
     */
    public function getArrayCollection()
    {
        $statuses = explode(',', $this->probanceHelper->getGivenFlowValue($this->flow, 'status'));
        $orderCollection = $this->orderCollectionFactory->create();

        if (isset($this->range['from']) && isset($this->range['to'])) {
            $orderCollection
                ->addFieldToFilter('updated_at', ['from' => $this->range['from']])
                ->addFieldToFilter('updated_at', ['to' => $this->range['to']]);
        }

        $orderCollection->addFieldToFilter('status', ['in' => $statuses]);

        return [
            [
                'object' => $orderCollection,
                'callback' => 'orderCallback',
            ],
        ];
    }
}