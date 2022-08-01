<?php

namespace Probance\M2connector\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Probance\M2connector\Model\SequenceFactory;
use Probance\M2connector\Model\ResourceModel\Sequence\CollectionFactory as SequenceCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Probance\M2connector\Model\Config\Source\Filename\Suffix;
use Probance\M2connector\Model\Config\Source\Cron\Frequency;

class Data extends AbstractHelper
{
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
     * @var SequenceFactory
     */
    protected $sequenceFactory;

    /**
     * @var SequenceCollectionFactory
     */
    protected $sequenceCollectionFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param SequenceFactory $sequenceFactory
     */
    public function __construct(
        Context $context,
        SequenceFactory $sequenceFactory,
        SequenceCollectionFactory $sequenceCollectionFactory
    )
    {
        $this->sequenceFactory = $sequenceFactory;
        $this->sequenceCollectionFactory = $sequenceCollectionFactory;
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
     * Return filename suffix
     *
     * @return false|string
     */
    public function getFilenameSuffix()
    {
        $suffix = $this->getFlowFormatValue('filename_suffix');

        switch ($suffix) {
            case Suffix::FILENAME_SUFFIX_YESTERDAY:
                $date = date('Ymd', strtotime($suffix . ' -1 day'));
                break;
            case Suffix::FILENAME_SUFFIX_TODAY:
                $date = date('Ymd');
                break;
            default:
                $date = date('Ymd');
        }

        return $date;
    }

    /**
     * Get export date range
     *
     * @return array
     */
    public function getExportRangeDate()
    {
        $suffix = $this->getFlowFormatValue('filename_suffix');

        $now = new \DateTime();

        switch ($suffix) {
            case Suffix::FILENAME_SUFFIX_YESTERDAY:
                $from = date('Y-m-d '. $now->format('H:i:s'), strtotime(date('Y-m-d') . ' -2 day'));
                $to = date('Y-m-d '. $now->format('H:i:s'), strtotime(date('Y-m-d') . ' -1 day'));
                break;
            case Suffix::FILENAME_SUFFIX_TODAY:
                $from = date('Y-m-d '. $now->format('H:i:s'), strtotime(date('Y-m-d') . ' -1 day'));
                $to = date('Y-m-d '. $now->format('H:i:s'), strtotime(date('Y-m-d')));
                break;
            default:
                $from = date('Y-m-d '. $now->format('H:i:s'), strtotime(date('Y-m-d') . ' -1 day'));
                $to = date('Y-m-d '. $now->format('H:i:s'), strtotime(date('Y-m-d')));
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
        $sequenceCollection = $this->sequenceCollectionFactory
            ->create()
            ->addFieldToFilter('flow', $flow)
            ->addFieldToFilter('created_at', ['eq' => date('Y-m-d')]);
        $sequence = $sequenceCollection->getFirstItem();

        $frequency = $this->getGivenFlowValue($flow, 'frequency');
        if ($frequency == Frequency::CRON_EVERY_HOUR) {
            $value = '.rt' . date('H') . substr(date('i'), 0, 1);
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
        $sequence = $this->sequenceFactory->create()->setData(
            [
                'flow' => $flow,
                'value' => $value,
                'created_at' => date('Y-m-d H:i:s'),
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
}
