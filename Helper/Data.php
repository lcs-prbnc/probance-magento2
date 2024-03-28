<?php

namespace Probance\M2connector\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Probance\M2connector\Model\SequenceFactory;
use Probance\M2connector\Model\ResourceModel\Sequence\CollectionFactory as SequenceCollectionFactory;
use Probance\M2connector\Model\Config\Source\Filename\Suffix;
use Probance\M2connector\Model\Config\Source\Cron\Frequency;

use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Model\LogFactory;
use Psr\Log\LoggerInterface; 

class Data extends AbstractHelper
{
    /**
     * XML Path to Log retention
     */
    const XML_PATH_LOG_RETENTION = 'probance/log/retention';

    /**
     * XML Path to FTP section
     */
    const XML_PATH_PROBANCE_FTP = 'probance/ftp/%s';

    /**
     * XML Path to API section
     */
    const XML_PATH_PROBANCE_API = 'probance/api/%s';

    /**
     * XML Path to WEBTRACKING section
     */
    const XML_PATH_PROBANCE_WEBTRACKING = 'probance/webtracking/%s';

    /**
     * XML Path to FLOW FORMAT section
     */
    const XML_PATH_PROBANCE_FLOW = 'probance/flow/%s';

    /**
     * XML Path to given FLOW section
     */
    const XML_PATH_PROBANCE_GIVEN_FLOW = 'probance/%s_flow/%s';

    /**
     * XML Path to DEBUG mode
     */
    const XML_PATH_PROBANCE_DEBUG = 'probance/flow/debug';

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var LogRepositoryInterface
     */
    protected $logRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SequenceFactory
     */
    protected $sequenceFactory;

    /**
     * @var SequenceCollectionFactory
     */
    protected $sequenceCollectionFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param SequenceFactory $sequenceFactory
     */
    public function __construct(
        Context $context,
        LogRepositoryInterface $logRepository,
        LogFactory $logFactory,
        LoggerInterface $logger,
        SequenceFactory $sequenceFactory,
        SequenceCollectionFactory $sequenceCollectionFactory,
        TimezoneInterface $timezone
    )
    {
        $this->logRepository = $logRepository;
        $this->logFactory = $logFactory;
        $this->logger = $logger;
        $this->sequenceFactory = $sequenceFactory;
        $this->sequenceCollectionFactory = $sequenceCollectionFactory;
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    /**
     * Get value in FTP section
     *
     * @param $code
     * @param null $website
     * @return mixed
     */
    public function getFtpValue($code, $website = null)
    {
        return $this->scopeConfig->getValue(sprintf(self::XML_PATH_PROBANCE_FTP, $code), ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * Get value in API section
     *
     * @param $code
     * @param null $website
     * @return mixed
     */
    public function getApiValue($code, $website = null)
    {
        return $this->scopeConfig->getValue(sprintf(self::XML_PATH_PROBANCE_API, $code), ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * Get value in WEBTRACKING section
     *
     * @param $code
     * @param null $website
     * @return mixed
     */
    public function getWebtrackingValue($code, $website = null)
    {
        return $this->scopeConfig->getValue(sprintf(self::XML_PATH_PROBANCE_WEBTRACKING, $code), ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * Get value in FLOW FORMAT section
     *
     * @param $code
     * @param null $website
     * @return mixed
     */
    public function getFlowFormatValue($code, $website = null)
    {
        return $this->scopeConfig->getValue(sprintf(self::XML_PATH_PROBANCE_FLOW, $code), ScopeInterface::SCOPE_WEBSITE, $website);
    }


    /**
     * Get value in given FLOW section
     *
     * @param $flow
     * @param $code
     * @param null $website
     * @return mixed
     */
    public function getGivenFlowValue($flow, $code, $website = null)
    {
        return $this->scopeConfig->getValue(sprintf(self::XML_PATH_PROBANCE_GIVEN_FLOW, $flow, $code), ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * Get datetime to use
     * 3rd parameter set to false to use specific timezone defined and not magento timezone
     * Magento timezone is more used for display, but database keep server timezone entries
     */
    public function getDatetime() 
    {
        return $this->timezone->date(null, null, false, true);
    }

    /**
     * Return filename suffix
     *
     * @return false|string
     */
    public function getFilenameSuffix()
    {
        $suffix = $this->getFlowFormatValue('filename_suffix');
        $now = $this->getDatetime();
        $onedaybefore = $this->getDatetime();
        $onedaybefore = $onedaybefore->sub(new \DateInterval('P1D'));

        switch ($suffix) {
            case Suffix::FILENAME_SUFFIX_YESTERDAY:
                $date = $onedaybefore->format('Ymd');
                break;
            case Suffix::FILENAME_SUFFIX_TODAY:
                $date = $now->format('Ymd');
                break;
            default:
                $date = $now->format('Ymd');
        }

        return $date;
    }

    /**
     * Get export date range
     *
     * @return array
     */
    public function getExportRangeDate($flow)
    {
        // Ensure to use specific timezone set
        $specific_timezone = $this->getFlowFormatValue('specific_timezone');
        date_default_timezone_set($specific_timezone);

        $now = $this->getDatetime();

        $frequency = $this->getGivenFlowValue($flow, 'frequency');
        if ($frequency == Frequency::CRON_DAILY_WITH_EVERY_HOUR) {
            // Corresponds to daily case
            $flow_time = $this->getGivenFlowValue($flow, 'time');
	    if (($flow_time != null) && $now->format('H') == substr($flow_time,0,2)) {
                $frequency = Frequency::CRON_DAILY;
            } else {
                $frequency = Frequency::CRON_EVERY_HOUR;
            }
        }
        $suffix = $this->getFlowFormatValue('filename_suffix');

        $range = false;
        switch ($frequency) {
            case Frequency::CRON_EVERY_HOUR:
                $range = $this->getExportRangeDateForFreq($now, $suffix, 'H', true);
                break;
            case Frequency::CRON_DAILY:
                $range = $this->getExportRangeDateForFreq($now, $suffix, 'D');
                break;
            case Frequency::CRON_WEEKLY:
                $range = $this->getExportRangeDateForFreq($now, $suffix, 'W');
                break;
            case Frequency::CRON_MONTHLY:
                $range = $this->getExportRangeDateForFreq($now, $suffix, 'M');
                break;
            default:
                $range = false;
        }
        return $range;
    }

    public function getExportRangeDateForFreq($now, $suffix, $period, $time=false)
    { 
        $twobefore = $this->getDatetime();
        $twobefore = $twobefore->sub(new \DateInterval('P'.($time ? 'T' : '').'2'.$period));
        $onebefore = $this->getDatetime();
        $onebefore = $onebefore->sub(new \DateInterval('P'.($time ? 'T' : '').'1'.$period));

        switch ($suffix) {
            case Suffix::FILENAME_SUFFIX_YESTERDAY:
                $from = $twobefore->format('Y-m-d H:i:s');
                $to = $onebefore->format('Y-m-d H:i:s');
                break;
            case Suffix::FILENAME_SUFFIX_TODAY:
                $from = $onebefore->format('Y-m-d H:i:s');
                $to = $now->format('Y-m-d H:i:s');
                break;
            default:
                $from = $onebefore->format('Y-m-d H:i:s');
                $to = $now->format('Y-m-d H:i:s');
        }
        return [
            'from' => $from,
            'to' => $to
        ];
    }

    /**
     * Retrieve sequence value
     *
     * @param $flow
     * @return string
     * @throws Exception
     */
    public function getSequenceValue($flow)
    {
        $value = '';
        $now = $this->getDatetime();

        $sequenceCollection = $this->sequenceCollectionFactory
            ->create()
            ->addFieldToFilter('flow', $flow)
            ->addFieldToFilter('created_at', ['eq' => $now->format('Y-m-d')]);
        $sequence = $sequenceCollection->getFirstItem();

        $frequency = $this->getGivenFlowValue($flow, 'frequency');
        if ($frequency == Frequency::CRON_DAILY_WITH_EVERY_HOUR) {
            // Corresponds to daily case
            $flow_time = $this->getGivenFlowValue($flow, 'time');
	    if (($flow_time != null) && $now->format('H') == substr($flow_time,0,2)) {
                $frequency = Frequency::CRON_DAILY;
            } else {
                $frequency = Frequency::CRON_EVERY_HOUR;
            }
        }

        if ($frequency == Frequency::CRON_EVERY_HOUR) {
            $value = '.rt' . $now->format('H') . substr($now->format('i'), 0, 1);
        } else {
            if ($sequenceCollection->count() > 0) {
                $loaded = $this->sequenceFactory->create()->load($sequence->getId());
                $loaded->setValue($sequence->getValue() + 1);
                $loaded->save();
                $value = $this->formatSequenceValue($loaded->getValue());
            } else {
                $this->setSequenceValue($flow, 0);
            }
        }
        
        return $value;
    }

    /**
     * Save sequence value
     *
     * @param $flow
     * @param $value
     * @return $this
     * @throws Exception
     */
    protected function setSequenceValue($flow, $value)
    {
        $now = $this->getDatetime();
        $sequence = $this->sequenceFactory->create()->setData(
            [
                'flow' => $flow,
                'value' => $value,
                'created_at' => $now->format('Y-m-d'),
            ]
        );

        return $sequence->save();
    }

    /**
     * Format sequence value
     *
     * @param $value
     * @return string
     */
    protected function formatSequenceValue($value)
    {
        if ($value < 10) {
            return '-' . str_pad($value, 2, "0", STR_PAD_LEFT);
        }

        return '-' . $value;
    }

    public function getDebugMode()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PROBANCE_DEBUG);
    }

    public function getLogRetention()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LOG_RETENTION);
    }

    /** 
     * Add Log in database and warn in log file
     * @param string Simple string or serialized data
     * @param string Flow filename if during flow execution
     * @return \Probance\M2connector\Helper\Data 
     */
    public function addLog($error, $filename='') 
    {
        $log = $this->logFactory->create();
        $log->setFilename($filename);
        $log->setErrors($error);
        $log->setCreatedAt(date('Y-m-d H:i:s'));
        $this->logRepository->save($log);
        $this->logger->warning($error);
        return $this;
    }
}
