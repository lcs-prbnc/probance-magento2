<?php

namespace Probance\M2connector\Model\Export;

use Symfony\Component\Console\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Probance\M2connector\Model\Ftp;
use Magento\Framework\Model\ResourceModel\Iterator;
use Probance\M2connector\Model\LogFactory;
use Probance\M2connector\Api\LogRepositoryInterface;

abstract class AbstractFlow
{
    /**
     * Directory export path
     */
    const EXPORT_DIRECTORY = 'probance/export';
    /**
     * Suffix use for filename defined configuration path
     */
    const EXPORT_CONF_FILENAME_SUFFIX = '';

    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = '';

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $range = [];

    /**
     * @var bool
     */
    protected $is_init = false;

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
     * @var array
     */
    protected $mapping;

    /**
     * @var AbstractCollection
     */
    protected $flowMappingCollectionFactory;

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
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        Iterator $iterator,
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
        $this->logFactory = $logFactory;
        $this->logRepository = $logRepository;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function export()
    {
        $directory = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . self::EXPORT_DIRECTORY;
        $sequence = ($this->is_init ? '' : $this->probanceHelper->getSequenceValue($this->flow));

        $sequenceSuffix = ($sequence != '') ? $sequence : '';

        $filename = $this->getFilename() . '_' . $this->probanceHelper->getFilenameSuffix() . $sequenceSuffix . '.csv';
        $filepath = $directory . DIRECTORY_SEPARATOR . $filename;

        if (!$this->file->isDirectory($directory)) {
            $this->file->createDirectory($directory, 0777);
        }

        $this->csv = $this->file->fileOpen($filepath, 'w+');
        $this->file->filePutCsv(
            $this->csv, $this->getHeaderData(),
            $this->probanceHelper->getFlowFormatValue('field_separator'),
            $this->probanceHelper->getFlowFormatValue('enclosure')
        );

        foreach ($this->getArrayCollection() as $collection) {
            $object = $collection['object'];
            $count = (isset($collection['count']) ? $collection['count'] : $object->count());

            if ($this->progressBar) {
                $this->progressBar->start($count ?: 1);
            }

            $this->iterator->walk($object->getSelect(), [[$this, $collection['callback']]]);

            if (count($this->errors) > 0) {
                $log = $this->logFactory->create();
                $log->setFilename($this->flow);
                $log->setErrors(serialize($this->errors));
                $log->setCreatedAt(date('Y-m-d H:i:s'));
                $this->logRepository->save($log);
            }

            if ($this->progressBar) {
                $this->progressBar->setMessage($filename . ' was created.', 'status');
            }
        }

        if ($this->file->isExists($filepath)) {
            if ($this->progressBar) {
                $this->progressBar->setMessage('Sending file by FTP', 'status');
            }
            $this->ftp->sendFile($filename, $filepath);
        }
        if ($this->progressBar) {
            $this->progressBar->finish();
        }
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

    public function setIsInit($is_init)
    {
        $this->is_init = $is_init;
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

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->probanceHelper->getGivenFlowValue($this->flow, 'filename'.$this::EXPORT_CONF_FILENAME_SUFFIX);
    }

    /**
     * @return array
     */
    public function getHeaderData()
    {
        $this->mapping = $this->flowMappingCollectionFactory
            ->create()
            ->setOrder('position', 'ASC')
            ->toArray();

        $header = [];

        foreach ($this->mapping['items'] as $row) {
            $header[] = $row['probance_attribute'];
        }

        return $header;
    }

    abstract public function getArrayCollection();
}
