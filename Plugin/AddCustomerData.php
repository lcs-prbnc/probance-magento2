<?php

namespace Probance\M2connector\Plugin;

class AddCustomerData
{
    public function __construct(
        protected \Magento\Customer\Model\Session $customerSession,
    )
    {
    }

    /**
     * @param Customer $subject
     * @param          $result
     *
     * @return mixed
     */
    public function afterGetSectionData(
        \Magento\Customer\CustomerData\Customer $subject,
                                            $result
    )
    {
        $result['email'] = $this->getCustomerEmail();

        return $result;
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomer()->getEmail() : '';
    }
}
