<?php
/*
This class is default one and must be extended by each integrated flow.
Following constants must be declared with good path
CRON_STRING_PATH ; CRON_MODEL_PATH ; SYSTEM_CONFIG_TIME_PATH ; SYSTEM_CONFIG_FREQUENCY_PATH
*/

namespace Probance\M2connector\Model\Config\Backend\Cron;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Probance\M2connector\Model\Config\Source\Cron\Frequency;

class Base extends Value
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/probance_export_xxxx/schedule/cron_expr';

    /**
     * Cron model path
     */
    const CRON_MODEL_PATH = 'crontab/default/jobs/probance_export_xxxx/run/model';

    /**
     * System config time path
     */
    const SYSTEM_CONFIG_TIME_PATH = 'groups/xxxx_flow/fields/time/value';
    /**
     * System config frequency path
     */
    const SYSTEM_CONFIG_FREQUENCY_PATH = 'groups/xxxx_flow/fields/frequency/value';

    /**
     * @var ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var string
     */
    protected $_runModelPath = '';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->_runModelPath = $runModelPath;
        $this->_configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        $time = $this->getData($this::SYSTEM_CONFIG_TIME_PATH);
        $frequency = $this->getData($this::SYSTEM_CONFIG_FREQUENCY_PATH);

        if ($frequency != Frequency::CRON_EVERY_HOUR) {
            $cronExprArray = [
                intval($time[1]),
                intval($time[0]),
                $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY ? '1' : '*',
                '*',
                $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY ? '1' : '*',
            ];

            $cronExprString = join(' ', $cronExprArray);
        } else {
            $cronExprString = '0 */1 * * *';
        }

        try {
            $this->_configValueFactory->create()->load(
                $this::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                $this::CRON_STRING_PATH
            )->save();
            $this->_configValueFactory->create()->load(
                $this::CRON_MODEL_PATH,
                'path'
            )->setValue(
                $this->_runModelPath
            )->setPath(
                $this::CRON_MODEL_PATH
            )->save();
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save the cron expression.'));
        }

        return parent::afterSave();
    }
}
