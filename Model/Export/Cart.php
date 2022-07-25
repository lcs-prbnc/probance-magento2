<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Quote\Model\QuoteRepository;
use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\LogFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Quote\Model\Quote;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Probance\M2connector\Model\Flow\Formater\CartFormater;
use Probance\M2connector\Model\ResourceModel\MappingCart\CollectionFactory as CartMappingCollectionFactory;
use Psr\Log\LoggerInterface;

class Cart extends AbstractFlow
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
    protected $flow = 'cart';

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var ItemCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var Quote\Item
     */
    private $quoteItem;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var CartFormater
     */
    private $cartFormater;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * Cart constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     * @param LoggerInterface $logger

     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param CartMappingCollectionFactory $cartMappingCollectionFactory
     * @param Quote $quote
     * @param Quote\Item $quoteItem
     * @param TypeFactory $typeFactory
     * @param CartFormater $cartFormater
     * @param QuoteRepository $quoteRepository
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

        QuoteCollectionFactory $quoteCollectionFactory,
        ItemCollectionFactory $itemCollectionFactory,
        CartMappingCollectionFactory $cartMappingCollectionFactory,
        Quote $quote,
        Quote\Item $quoteItem,
        TypeFactory $typeFactory,
        CartFormater $cartFormater,
        QuoteRepository $quoteRepository
    )
    {
        $this->flowMappingCollectionFactory = $cartMappingCollectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->quote = $quote;
        $this->quoteItem = $quoteItem;
        $this->typeFactory = $typeFactory;
        $this->cartFormater = $cartFormater;
        $this->quoteRepository = $quoteRepository;

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
     * Cart callback
     *
     * @param array $args
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function cartCallback($args)
    {
        try {
            $quoteId = $args['row']['entity_id'];
            $allItems = $this
                ->getQuoteItemCollection($quoteId)
                ->getItems();

            $productsRelation = [];

            foreach ($allItems as $item) {
                if ($item->getParentItemId()) {
                    $parent = $this->quoteItem->load($item->getParentItemId());
                    $productsRelation[$parent->getProductId()] = $item->getProductId();
                }
            }

            $this->cartFormater->setProductRelation($productsRelation);

            $quote = null;
            $data = [];
            foreach ($allItems as $item) {
                if (!$item->isDeleted() && !$item->getParentItemId() && !$item->getParentItem()) {
                    if (!$quote) {
                        $quote = $this->quoteRepository->get($quoteId);
                        $this->cartFormater->setQuote($quote);
                    }

                    foreach ($this->mapping['items'] as $mappingItem) {
                        $key = $mappingItem['magento_attribute'];
                        $dataKey = $key . '-' . $mappingItem['position'];
                        $method = 'get' . $this->cartFormater->convertToCamelCase($key);
                        $data[$dataKey] = '';

                        if (!empty($mappingItem['user_value'])) {
                            $data[$dataKey] = $mappingItem['user_value'];
                            continue;
                        }

                        if (method_exists($this->cartFormater, $method)) {
                            $data[$dataKey] = $this->cartFormater->$method($item);
                        } else if (method_exists($item, $method)) {
                            $data[$dataKey] = $item->$method();
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
                }

                if ($this->progressBar) {
                    $this->progressBar->setMessage('Exporting product: ' . $item->getSku(), 'status');
                }
            }
            if ($this->progressBar) {
                $this->progressBar->advance();
            }
            unset($allItems);
            unset($quote);
            unset($data);

        } catch (\Exception $e) {
            $this->errors[] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
        }
    }

    /**
     * @param $quoteId
     * @return \Magento\Quote\Model\ResourceModel\Quote\Item\Collection
     */
    public function getQuoteItemCollection($quoteId)
    {
        return $this->itemCollectionFactory
            ->create()
            ->addFieldToFilter('quote_id', $quoteId);
    }

    /**
     * @return array
     */
    public function getArrayCollection()
    {
        $collection = $this->quoteCollectionFactory
            ->create()
            ->addFieldToFilter('customer_id', ['neq' => null]);

        if (isset($this->range['to']) && isset($this->range['from'])) {
            $collection
                ->addFieldToFilter('customer_id', ['neq' => null])
                ->addFieldToFilter('updated_at', ['from' => $this->range['from']])
                ->addFieldToFilter('updated_at', ['to' => $this->range['to']]);
        }

        return [
            [
                'object' => $collection,
                'callback' => 'cartCallback',
            ]
        ];
    }
}
