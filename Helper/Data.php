<?php

namespace Walkwizus\Probance\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Walkwizus\Probance\Model\SequenceFactory;
use Walkwizus\Probance\Model\ResourceModel\Sequence\CollectionFactory as SequenceCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Walkwizus\Probance\Model\Config\Source\Filename\Suffix;
use Walkwizus\Probance\Model\Config\Source\Cron\Frequency;

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
     * XML Path to CATALOG FLOW section
     */
    const XML_PATH_PROBANCE_CATALOG_FLOW = 'probance/catalog_flow/%s';

    /**
     * XML Path to CUSTOMER FLOW section
     */
    const XML_PATH_PROBANCE_CUSTOMER_FLOW = 'probance/customer_flow/%s';

    /**
     * XML Path to ORDER FLOW section
     */
    const XML_PATH_PROBANCE_ORDER_FLOW = 'probance/order_flow/%s';

    /**
     * XML Path to CART FLOW section
     */
    const XML_PATH_PROBANCE_CART_FLOW = 'probance/cart_flow/%s';

    /**
     * @var SequenceFactory
     */
    private $sequenceFactory;

    /**
     * @var SequenceCollectionFactory
     */
    private $sequenceCollectionFactory;

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
     * Get value in CATALOG FLOW section
     *
     * @param $code
     * @param null $website
     * @return mixed
     */
    public function getCatalogFlowValue($code, $website = null)
    {
        return $this->scopeConfig->getValue(sprintf(self::XML_PATH_PROBANCE_CATALOG_FLOW, $code), ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * Get value in CUSTOMER FLOW section
     * @param $code
     * @param null $website
     * @return mixed
     */
    public function getCustomerFlowValue($code, $website = null)
    {
        return $this->scopeConfig->getValue(sprintf(self::XML_PATH_PROBANCE_CUSTOMER_FLOW, $code), ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * Get value in ORDER FLOW section
     *
     * @param $code
     * @param null $website
     * @return mixed
     */
    public function getOrderFlowValue($code, $website = null)
    {
        return $this->scopeConfig->getValue(sprintf(self::XML_PATH_PROBANCE_ORDER_FLOW, $code), ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * Get value in CART FLOW section
     *
     * @param $code
     * @param null $website
     * @return mixed
     */
    public function getCartFlowValue($code, $website = null)
    {
        return $this->scopeConfig->getValue(sprintf(self::XML_PATH_PROBANCE_CART_FLOW, $code), ScopeInterface::SCOPE_WEBSITE, $website);
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

        switch ($suffix) {
            case Suffix::FILENAME_SUFFIX_YESTERDAY:
                $from = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . ' -2 day'));
                $to = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . ' -1 day'));
                break;
            case Suffix::FILENAME_SUFFIX_TODAY:
                $from = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . ' -1 day'));
                $to = date('Y-m-d 23:59:59', strtotime(date('Y-m-d')));
                break;
            default:
                $from = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . ' -1 day'));
                $to = date('Y-m-d 23:59:59', strtotime(date('Y-m-d')));
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
        $sequenceCollection = $this->sequenceCollectionFactory
            ->create()
            ->addFieldToFilter('flow', $flow)
            ->addFieldToFilter('created_at', ['eq' => date('Y-m-d')]);

        $sequence = $sequenceCollection->getFirstItem();

        $frequency = '';

        switch ($flow) {
            case 'customer':
                $frequency = $this->getCustomerFlowValue('frequency');
                break;
            case 'catalog':
                $frequency = $this->getCatalogFlowValue('frequency');
                break;
            case 'order':
                $frequency = $this->getOrderFlowValue('frequency');
                break;
            case 'cart':
                $frequency = $this->getCartFlowValue('frequency');
                break;
        }

        if ($sequenceCollection->count() > 0) {
            if ($frequency == Frequency::CRON_EVERY_HOUR) {
                return '.rt' . date('H') . substr(date('i'), 0, 1);
            }

            $loaded = $this->sequenceFactory->create()->load($sequence->getId());
            $loaded->setValue($sequence->getValue() + 1);
            $loaded->save();

            return $this->formatSequenceValue($loaded->getValue());
        } else {
            if ($frequency == Frequency::CRON_EVERY_HOUR) {
                return '.rt' . date('H') . substr(date('i'), 0, 1);
            } else {
                $value = 0;
            }

            $this->setSequenceValue($flow, $value);
            return '';
        }

        return $this->formatSequenceValue($sequence->getValue());
    }

    /**
     * Save sequence value
     *
     * @param $flow
     * @param $value
     * @return $this
     * @throws Exception
     */
    private function setSequenceValue($flow, $value)
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
    private function formatSequenceValue($value)
    {
        if ($value < 10) {
            return '-' . str_pad($value, 2, "0", STR_PAD_LEFT);
        }

        return '-' . $value;
    }
}