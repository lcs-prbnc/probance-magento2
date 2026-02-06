<?php

namespace Probance\M2connector\Model\Export;

use Symfony\Component\Console\Output\OutputInterface;
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
     * Default limit for export
     */
    const EXPORT_DEFAULT_LIMIT = 5000;

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
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $entityId = 0;

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
     * @var OutputInterface
     */
    protected $output = null;    

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
     * @var string
     */
    public $currentFilename;

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
     * @var int
     */
    public $nextPage = null;

    /**
     * @var bool
     */
    public $debug = false;

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
     * Set filter limit
     *
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)    
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set filter entityId
     *
     * @param $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
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
     * @param OutputInterface $output
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;
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
     * @param string
     */
    public function setCurrentFilename($filename)
    {
        $this->currentFilename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentFilename()
    {
        return $this->currentFilename;
    }

    /**
     * @param int
     */
    public function setNextPage($nextPage)
    {
        $this->nextPage = $nextPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getNextPage()
    {
        return $this->nextPage;
    }

    /**
     * @return array 
     */
    public function getMapping()
    {
        $this->mapping = $this->flowMappingCollectionFactory
            ->create()
            ->setOrder('position', 'ASC')
            ->toArray();
        if ($this->debug) {
            $this->probanceHelper->addLog(__('Mapping class used is %1',get_class($this->flowMappingCollectionFactory)), $this->flow);
        }
        return $this->mapping;
    }

    /**
     * @return array
     */
    public function getHeaderData()
    {
        $header = [];

        foreach ($this->mapping['items'] as $row) {
            $header[] = $row['probance_attribute'];
        }

        return $header;
    }

    /**
     * Case of attribute on other related entity
     * @param string $attribute : mapping magento_attribute
     * @return array with extracted key and subattribute
     */
    public function getSubAttribute($attribute)
    {
        // Case of other related entity
        $subAttribute = '';
        $key = $attribute;
        if (($diesePos = strpos($attribute, '##')) !== false) {
            $subAttribute = substr($attribute,$diesePos+2);
            $key = substr($attribute, 0, $diesePos);
        }
        return [$key, $subAttribute];
    }

    abstract public function getArrayCollection($storeId);

    /** 
     * Check for nextPage needs according to limit
     * @param \Magento\Framework\Data\Collection $collection
     * @return int $currentPage
     */
    public function checkForNextPage($collection)
    {
        // If collection size over limit set pagination
        if (!$this->limit) {
            $confLimit = $this->probanceHelper->getLimitOnCollection($this->probanceHelper->getFlowStore());
            if (!$confLimit) $this->limit = self::EXPORT_DEFAULT_LIMIT;
            else $this->limit = $confLimit;
        }
        $currentPage = 1;
        if ($this->getNextPage()) {
            $currentPage = $this->getNextPage();
        }

        $collectionSize = ($this->getNextPage() === 0) ? $this->limit : $collection->getSize();

        if ($collectionSize > ($this->limit * $currentPage)) {
            $this->setNextPage($currentPage + 1);
        } else {
            $this->setNextPage(null);
        }

        $collection->setPageSize($this->limit)->setCurPage($currentPage);

        if ($currentPage === 1) {
            if ($this->output) $this->output->writeln('<comment>' . __('Found %1 elements to treat for %2.', $collectionSize, $this->flow) . '</comment>');
        }

        return $currentPage;
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
        $this->debug = $this->probanceHelper->getDebugMode($storeId);
        
        $enabled = $this->probanceHelper->getGivenFlowValue($this->flow, 'enabled');
        if (!$enabled) {
            if ($this->debug) {
                $this->probanceHelper->addLog(__('Flow is not enabled for store %1',$storeId), $this->flow);
            }
            return;
        }

        $freq = $this->probanceHelper->getGivenFlowValue($this->flow, 'frequency');
        $this->probanceHelper->addLog(__('Exporting for %1 with frequency %2',get_class($this),$freq), $this->flow);

        $directory = $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . self::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . $storeId;

        // Force filename if following previous export
        if ($this->getNextPage()) {
            $filenamePage = $this->getCurrentFilename();
            $filename = $filenamePage ?? $filename;
        } else {
            $sequence = ($this->is_init ? '' : $this->probanceHelper->getSequenceValue($this->flow, $storeId, $this->is_sameseq));
            $sequenceSuffix = ($sequence != '') ? $sequence : '';
            // Build csv filename for export
            $filename = $this->getFilename() . '_' . $this->probanceHelper->getFilenameSuffix() . $sequenceSuffix . '.csv';
            $this->setCurrentFilename($filename);
        }
        $filepath = $directory . DIRECTORY_SEPARATOR . $filename;
        if (!$this->file->isDirectory($directory)) {
            $this->file->createDirectory($directory, 0777);
        }
        if (!$this->getNextPage()) {
            $this->csv = $this->file->fileOpen($filepath, 'w');
            if ($this->output) $this->output->writeln('<comment>'.__('%1 was created.', $filename).'</comment>');
        } else {
            $this->csv = $this->file->fileOpen($filepath, 'a');
        }

        // Retrieve mapping, to be done before getHeaderData or iterate
        $this->getMapping();

        if (!$this->getNextPage()) {
            @fputcsv(
                $this->csv, $this->getHeaderData(),
                $this->probanceHelper->getFlowFormatValue('field_separator'),
                $this->probanceHelper->getFlowFormatValue('enclosure')
            );
        }

        foreach ($this->getArrayCollection($storeId) as $collection) 
        {
            try {
                $object = $collection['object'];
                $count = $collection['count'];
                if ($this->getNextPage() === 2) {
                    $nbPages = floor($count / $this->limit) +1;
                    if ($this->output) $this->output->writeln('<comment>'.__('Limit set to %1 so pagination will be done with %2 pages.', $this->limit, $nbPages).'</comment>');
                }

                if ($this->debug) {
                    $this->probanceHelper->addLog(__('Flow %3 count elements is : %1 // Using this request : %2',$count,$collection['object']->getSelect(),$this->flow), $this->flow);
                }

                if ($this->progressBar) {
                    $this->iterator->setProgressBar($this->progressBar);
                    $this->progressBar->setMessage(__('Starting %1 export...',$collection['callback']), 'status');
                    $this->progressBar->start($this->limit ?: 1);
                }

                $this->iterator->walk($object, [$this, $collection['callback']]);
                $nbErrors = count($this->errors);
                if ($nbErrors > 0) {
                    $chunked = array_chunk($this->errors, 10);
                    foreach ($chunked as $chunk) {
                        $this->probanceHelper->addLog(serialize($chunk), $this->flow);
                    }
                    $this->errors = [];                    
                }

                if ($this->progressBar) {
                    $this->progressBar->setMessage(__('%1 export ended, with %2 errors', $collection['callback'], $nbErrors), 'status');
                    $this->progressBar->finish();
                }
            } catch (\Exception $e) {
                $this->probanceHelper->addLog(serialize([
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]), $this->flow);
            }
        }

        // No sendFTP if nextPage set
        if (!$this->getNextPage()) {
            if ($this->file->isExists($filepath)) {
                if ($this->progressBar) {
                    $this->progressBar->setMessage(__('Sending file by FTP'), 'status');
                }
                $this->ftp->sendFile($storeId, $filename, $filepath);
            }
        }
        if ($this->progressBar && !$this->getNextPage()) { 
            $this->progressBar->finish();
        }
    }
}
