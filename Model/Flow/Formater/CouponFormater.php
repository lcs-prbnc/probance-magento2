<?php

namespace Walkwizus\Probance\Model\Flow\Formater;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleRepository;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponRepository;
use Magento\Customer\Api\GroupRepositoryInterface;

class CouponFormater extends AbstractFormater
{
    /**
     * @var Rule
     */
    private $rule;

    /**
     * @var GroupRepositoryInterface
     */
    private $customerGroupRepository;

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
        $customerGroupId = $this->rule->getCustomerGroupId();
        $customerGroupCode = '';
        if ($customerGroupId) {
            $customerGroup = $this->customerGroupRepository->getById($customerGroupId);
            if ($customerGroup) $customerGroupCode = $customerGroup->getCode();
        }
        return $customerGroupCode;
    }

}
