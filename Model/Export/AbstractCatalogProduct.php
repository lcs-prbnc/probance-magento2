<?php

namespace Walkwizus\Probance\Model\Export;

use Symfony\Component\Console\Helper\ProgressBar;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Walkwizus\Probance\Model\Ftp;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Walkwizus\Probance\Model\LogFactory;
use Walkwizus\Probance\Api\LogRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

abstract class AbstractCatalogProduct
{
    /**
     * Directory export path
     */
    const EXPORT_DIRECTORY = 'probance/export';

    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'catalog';

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $range = [];

    /**
     * @var ProgressBar|bool
     */
    protected $progressBar = false;

    /**
     * @var resource file
     */
    protected $csv;

    /**
     * @var ProbanceHelper
     */
    protected $probanceHelper;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Ftp
     */
    protected $ftp;

    /**
     * @var Iterator
     */
    protected $iterator;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @var LogRepositoryInterface 
     */
    private $logRepository;

    /**
     * AbstractCatalogProduct constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator
     * @param ProductCollection $productCollection
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        Iterator $iterator,
        ProductCollection $productCollection,
        LogFactory $logFactory,
        LogRepositoryInterface $logRepository
    )
    {
        $this->errors = [];
        $this->probanceHelper = $probanceHelper;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->ftp = $ftp;
        $this->iterator = $iterator;
        $this->productCollection = $productCollection;
        $this->logFactory = $logFactory;
        $this->logRepository = $logRepository;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function export()
    {
        $directory = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . self::EXPORT_DIRECTORY;
        $sequence = $this->probanceHelper->getSequenceValue($this->flow);

        $sequenceSuffix = ($sequence != '') ? $sequence : '';

        $filename = $this->getFilename() . '_' . $this->probanceHelper->getFilenameSuffix() . $sequenceSuffix . '.csv';
        $filepath = $directory . '/' . $filename;

        if (!$this->file->isDirectory($directory)) {
            $this->file->createDirectory($directory, 0777);
        }

        $this->csv = $this->file->fileOpen($filepath, 'w+');
        $this->file->filePutCsv(
            $this->csv, $this->getHeaderData(),
            $this->probanceHelper->getFlowFormatValue('field_separator'),
            $this->probanceHelper->getFlowFormatValue('enclosure')
        );

        $collection = $this->getProductCollection();

        if ($this->progressBar) {
            $this->progressBar->setMaxSteps($collection->count() ?: 1);
            $this->progressBar->start();
        }

        $this->iterator->walk($this->getProductCollection()->getSelect(), [[$this, 'iterateCallback']]);

        if (count($this->errors) > 0) {
            $log = $this->logFactory->create();
            $log->setFilename($this->flow);
            $log->setErrors(serialize($this->errors));
            $log->setCreatedAt(date('Y-m-d H:i:s'));
            $this->logRepository->save($log);
        }

        if ($this->progressBar) {
            $this->progressBar->setMessage($filename . ' was created.', 'status');
            $this->progressBar->finish();
        }

        if ($this->file->isExists($filepath)) {
            $this->ftp->sendFile($filename, $filepath);
        }
    }

    /**
     * @return ProductCollection
     */
    protected function getProductCollection()
    {
        if (isset($this->range['from']) && isset($this->range['to'])) {
            $this->productCollection
                ->addAttributeToFilter('updated_at', ['from' => $this->range['from']])
                ->addAttributeToFilter('updated_at', ['to' => $this->range['to']]);
        }

        $this->productCollection->addAttributeToFilter('status', Status::STATUS_ENABLED);

        return $this->productCollection;
    }

    /**
     * Set filter range
     *
     * @param $from
     * @param $to
     * @return $this
     */
    public function setRange($from, $to)
    {
        $this->range['from'] = $from;
        $this->range['to'] = $to;

        return $this;
    }

    /**
     * @param ProgressBar $progressBar
     * @return $this
     */
    public function setProgressBar(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;

        return $this;
    }

    abstract public function getFilename();
    abstract public function getHeaderData();
    abstract public function iterateCallback($args);
}