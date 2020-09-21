<?php

namespace Walkwizus\Probance\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Quote\Model\QuoteRepository;
use Walkwizus\Probance\Api\LogRepositoryInterface;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\Ftp;
use Walkwizus\Probance\Model\LogFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Quote\Model\Quote;
use Walkwizus\Probance\Model\Flow\Type\Factory as TypeFactory;
use Walkwizus\Probance\Model\Flow\Formater\CartFormater;
use Walkwizus\Probance\Model\ResourceModel\MappingCart\CollectionFactory as CartMappingCollectionFactory;

class Cart extends AbstractFlow
{
    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'cart';

    /**
     * @var array
     */
    private $mapping;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var ItemCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var CartMappingCollectionFactory
     */
    private $cartMappingCollectionFactory;

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
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->cartMappingCollectionFactory = $cartMappingCollectionFactory;
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
            $logRepository
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

            foreach ($allItems as $allItem) {
                if ($allItem->getParentItemId()) {
                    $parent = $this->quoteItem->load($allItem->getParentItemId());
                    $productsRelation[$parent->getProductId()] = $allItem->getProductId();
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
                }

                if ($this->progressBar) {
                    $this->progressBar->setMessage('Exporting product: ' . $item->getSku(), 'status');
                    $this->progressBar->advance();
                }
            }
        } catch (\Exception $e) {

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

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->probanceHelper->getCartFlowValue('filename');
    }

    /**
     * @return array
     */
    public function getHeaderData()
    {
        $this->mapping = $this->cartMappingCollectionFactory
            ->create()
            ->setOrder('position', 'ASC')
            ->toArray();

        $header = [];

        foreach ($this->mapping['items'] as $row) {
            $header[] = $row['probance_attribute'];
        }

        return $header;
    }
}
