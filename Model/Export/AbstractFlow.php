<?php

namespace Probance\M2connector\Model\Export;

use Symfony\Component\Console\Helper\ProgressBar;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\BatchIterator as Iterator;

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
     * @var bool
     */
    protected $is_sameseq = false;

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
     * AbstractCatalogProduct constructor.
     *
     * @param ProbanceHelper $probanceHelper
     * @param DirectoryList $directoryList
     * @param File $file
     * @param Ftp $ftp
     * @param Iterator $iterator
     */
    public function __construct(
        ProbanceHelper $probanceHelper,
        DirectoryList $directoryList,
        File $file,
        Ftp $ftp,
        Iterator $iterator
    )
    {
        $this->errors = [];
        $this->probanceHelper = $probanceHelper;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->ftp = $ftp;
        $this->iterator = $iterator;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function export($storeId=null, $sameSeq=false)
    {
        $this->setIsSameseq($sameSeq);
        if ($storeId) {
            $this->exportForStore($storeId);
        } else {
            foreach ($this->probanceHelper->getStoresList() as $store) {
                $this->exportForStore($store->getId());
            }
        }
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function exportForStore($storeId)
    {
        $this->probanceHelper->setFlowStore($storeId);
        $debug = $this->probanceHelper->getDebugMode($storeId);
        
        $enabled = $this->probanceHelper->getGivenFlowValue($this->flow, 'enabled');
        if (!$enabled) {
            if ($debug) {
                $this->probanceHelper->addLog('Flow is not enabled for store '.$storeId, $this->flow);
            }
            return;
        }

        $freq = $this->probanceHelper->getGivenFlowValue($this->flow, 'frequency');
        $this->probanceHelper->addLog('Exporting for '.get_class($this). ' with frequency '.$freq, $this->flow);

        $directory = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . self::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . $storeId;
        $sequence = ($this->is_init ? '' : $this->probanceHelper->getSequenceValue($this->flow, $storeId, $this->is_sameseq));
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

        
        foreach ($this->getArrayCollection($storeId) as $collection) 
        {
            try {
                $object = $collection['object'];
                if (isset($collection['count'])) $count = $collection['count'];
                else {
                    if (method_exists($object,'getSize')) {
                        $count = $object->getSize();
                    } else {
                        $count = $object->count();
                    }
                    $object->clear();
                }
                if ($debug) {
                    $this->probanceHelper->addLog('Flow count elements is :'.$count.' // Using this request : '.$collection['object']->getSelect().'', $this->flow);
                    $this->iterator->setProgressBar($this->progressBar);
                }

                if ($this->progressBar) {
                    $this->progressBar->setMessage('Starting '.$collection['callback'].' export...', 'status');
                    $this->progressBar->start($count ?: 1);
                }

                $this->iterator->walk($object, [$this, $collection['callback']]);
                if (count($this->errors) > 0) {
                    $chunked = array_chunk($this->errors, 10);
                    foreach ($chunked as $chunk) {
                        $this->probanceHelper->addLog(serialize($chunk), $this->flow);
                    }
                }

                if ($this->progressBar) {
                    $this->progressBar->setMessage($filename . ' was created.', 'status');
                }
            } catch (\Exception $e) {
                $this->probanceHelper->addLog(serialize([
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]), $this->flow);
            }
        }

        if ($this->file->isExists($filepath)) {
            if ($this->progressBar) {
                $this->progressBar->setMessage('Sending file by FTP', 'status');
            }
            $this->ftp->sendFile($storeId, $filename, $filepath);
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

    public function setIsSameseq($is_sameseq)
    {
        $this->is_sameseq = $is_sameseq;
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

    abstract public function getArrayCollection($storeId);
}
