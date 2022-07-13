<?php

namespace Probance\M2connector\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\SalesRule\Model\Coupon as SalesRuleCoupon;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\CouponRepository;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory as CouponCollectionFactory;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;
use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\LogFactory;
use Probance\M2connector\Model\Flow\Type\Factory as TypeFactory;
use Probance\M2connector\Model\Flow\Formater\CouponFormater;
use Probance\M2connector\Model\ResourceModel\MappingCoupon\CollectionFactory as CouponMappingCollectionFactory;
use Psr\Log\LoggerInterface;

class Coupon extends AbstractFlow
{
    const EXPORT_CONF_FILENAME_SUFFIX = ''; 

    /**
     * Flow type
     *
     * @var string
     */
    protected $flow = 'coupon';

    /**
     * @var CouponCollectionFactory
     */
    private $couponCollectionFactory;

    /**
     * @var RuleCollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var CouponRepository
     */
    private $couponRepository;

    /**
     * @var SalesRuleCoupon
     */
    private $coupon;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Rule
     */
    private $rule;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var CouponFormater
     */
    private $couponFormater;

    /**
     * @var CustomerGroupRepository
     */
    private $customerGroupRepository;

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

     * @param CouponCollectionFactory $couponCollectionFactory
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param CouponRepository $couponRepository
     * @param RuleRepository $ruleFactory
     * @param SalesRuleCoupon $coupon
     * @param Rule $rule
     * @param TypeFactory $typeFactory
     * @param CouponFormater $couponFormater
     * @param CouponMappingCollectionFactory $couponMappingCollectionFactory
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

        CouponCollectionFactory $couponCollectionFactory,
        RuleCollectionFactory $ruleCollectionFactory,
        CouponRepository $couponRepository,
        RuleFactory $ruleFactory,
        SalesRuleCoupon $coupon,
        Rule $rule,
        TypeFactory $typeFactory,
        CouponFormater $couponFormater,
        CouponMappingCollectionFactory $couponMappingCollectionFactory,
        CustomerGroupRepository $customerGroupRepository
    )
    {
        $this->flowMappingCollectionFactory = $couponMappingCollectionFactory;
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->couponRepository = $couponRepository;
        $this->ruleFactory = $ruleFactory;
        $this->coupon = $coupon;
        $this->rule = $rule;
        $this->typeFactory = $typeFactory;
        $this->couponFormater = $couponFormater;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->couponFormater->setCustomerGroupRepository($customerGroupRepository);

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
     * Coupon callback
     *
     * @param array $args
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function iterateCallback($args)
    {
        try {
            $ruleId = $args['row']['rule_id'];
            $rule = $this->ruleFactory->create()->load($ruleId);
            if (!$rule->getId()) throw new Exception('Rule unknown'); 
            $allItems = $this
                ->getCouponCollection($ruleId)
                ->getItems();

            $this->couponFormater->setRule($rule);
            $this->couponFormater->setHelper($this->probanceHelper);
            $data = [];
            foreach ($allItems as $item) {
                if ($this->progressBar) {
                    $this->progressBar->setMessage('Exporting Rule: '. $ruleId .' Coupon: ' . $item->getCode(), 'status');
                }
                foreach ($this->mapping['items'] as $mappingItem) {
                    $key = $mappingItem['magento_attribute'];
                    $dataKey = $key . '-' . $mappingItem['position'];
                    $objectSource = $item;
                    $method = 'get' . $this->couponFormater->convertToCamelCase($key);
                    if (strpos($key, "rule.") === 0) {
                        $objectSource = $rule;
                        $method = 'get' . $this->couponFormater->convertToCamelCase(substr($key,5));
                    }
                    $data[$dataKey] = '';

                    if (!empty($mappingItem['user_value'])) {
                        $data[$dataKey] = $mappingItem['user_value'];
                        continue;
                    }

                    if (method_exists($this->couponFormater, $method)) {
                        $data[$dataKey] = $this->couponFormater->$method($item);
                    } else {
                        $data[$dataKey] = $objectSource->$method();
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
                $this->progressBar->advance();
            }
            unset($rule);
            unset($allItems);
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
    public function getCouponCollection($ruleId)
    {
        return $this->couponCollectionFactory
            ->create()
            ->addFieldToFilter('rule_id', $ruleId);
    }

    /**
     * @return array
     */
    public function getArrayCollection()
    {
        $collection = $this->ruleCollectionFactory
            ->create()
            ->addFieldToFilter('coupon_type', ['neq' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON]);

        if (isset($this->range['to']) && isset($this->range['from'])) {
            $collection
                ->addFieldToFilter('from_date', ['from' => $this->range['from']])
                ->addFieldToFilter('to_date', ['to' => $this->range['to']]);
        }

        return [
            [
                'object' => $collection,
                'callback' => 'iterateCallback',
            ]
        ];
    }
}
