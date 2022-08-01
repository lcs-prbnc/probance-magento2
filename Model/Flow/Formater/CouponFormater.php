<?php

namespace Probance\M2connector\Model\Flow\Formater;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleRepository;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponRepository;
use Magento\Customer\Api\GroupRepositoryInterface;
use Probance\M2connector\Helper\Data as ProbanceHelper;

class CouponFormater extends AbstractFormater
{
    /**
     * @var ProbanceHelper
     */
    protected $helper;

    /**
     * @var Rule
     */
    protected $rule;

    /**
     * @var GroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @param Rule $rule
     */
    public function setHelper(ProbanceHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param Rule $rule
     */
    public function setRule(Rule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * @param GroupRepositoryInterface $groupRepository
     */
    public function setCustomerGroupRepository(GroupRepositoryInterface $groupRepository)
    {
        $this->customerGroupRepository = $groupRepository;
    }

    /**
     * Get Customer Group Code
     *
     * @param $item
     * @return string
     */
    public function getCustomerGroupCode($item)
    {
        $sep = $this->helper->getFlowFormatValue('inner_field_separator');
        $customerGroupIds = $this->rule->getCustomerGroupIds();
        $customerGroupCode = '';
        foreach ($customerGroupIds as $customerGroupId) {
            $customerGroup = $this->customerGroupRepository->getById($customerGroupId);
            if ($customerGroup) $customerGroupCode .= $customerGroup->getCode() . $sep;
        }
        $customerGroupCode = substr($customerGroupCode, 0 ,-1);
        return $customerGroupCode;
    }

}
