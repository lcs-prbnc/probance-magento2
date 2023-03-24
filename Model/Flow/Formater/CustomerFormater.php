<?php

namespace Probance\M2connector\Model\Flow\Formater;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Eav\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CustomerFormater extends AbstractFormater
{
    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    protected $eavConfig;

    /**
     * CustomerFormater constructor.
     *
     * @param SubscriberFactory $subscriberFactory
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        SubscriberFactory $subscriberFactory,
        AddressRepositoryInterface $addressRepository,
        Config $config,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->subscriberFactory = $subscriberFactory;
        $this->addressRepository = $addressRepository;
        $this->eavConfig = $config;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Rule $rule
     */
    public function setHelper(ProbanceHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Format created_at attribute
     *
     * @param CustomerInterface $customer
     * @return string
     * @throws \Exception
     */
    public function getCreatedAt(CustomerInterface $customer)
    {
        $datetime = new \DateTime($customer->getCreatedAt());
        return $datetime->format('Y-m-d');
    }

    /**
     * Get is optin flag
     *
     * @param CustomerInterface $customer
     * @return int
     */
    public function getOptinFlag(CustomerInterface $customer)
    {
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByCustomer($customer->getId(),$customer->getWebsiteId());

        if ($subscriber->isSubscribed()) {
            return 1;
        }

        return 0;
    }

    /**
     * Get billing address company
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getBillingAddressCompany(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultBilling();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getCompany();
    }

    /**
     * Get billing address city
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getBillingAddressCity(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultBilling();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getCity();
    }

    /**
     * Get billing address country (ISO 2 letters)
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getBillingAddressCountry(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultBilling();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getCountryId();
    }

    /**
     * Get billing address state
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getBillingAddressState(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultBilling();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getRegion()->getRegionCode();
    }

    /**
     * Get billing address postcode
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getBillingAddressPostcode(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultBilling();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getPostcode();
    }

    /**
     * Get billing address phone
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getBillingAddressPhone(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultBilling();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getTelephone() ?: '';
    }

    /**
     * Get shipping address company
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getShippingAddressCompany(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultShipping();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getCompany();
    }

    /**
     * Get shipping address city
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getShippingAddressCity(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultShipping();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getCity();
    }

    /**
     * Get shipping address country (ISO 2 letters)
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getShippingAddressCountry(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultShipping();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getCountryId();
    }

    /**
     * Get shipping address state
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getShippingAddressState(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultShipping();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getRegion()->getRegionCode();
    }

    /**
     * Get billing address postcode
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getShippingAddressPostcode(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultShipping();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getPostcode();
    }

    /**
     * Get billing address phone
     *
     * @param CustomerInterface $customer
     * @return null|string
     * @throws LocalizedException
     */
    public function getShippingAddressPhone(CustomerInterface $customer)
    {
        $addressId = $customer->getDefaultShipping();

        if (!$addressId) {
            return '';
        }

        $address = $this->addressRepository->getById($addressId);

        return $address->getTelephone();
    }

    /**
     * Get customer gender as text
     *
     * @param CustomerInterface $customer
     * @return mixed
     * @throws LocalizedException
     */
    public function getGender(CustomerInterface $customer)
    {
        $attribute = $this->eavConfig->getAttribute('customer', 'gender');
        return $attribute->getSource()->getOptionText($customer->getGender());
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
    public function getCustomerGroupCode(CustomerInterface $customer)
    {
        $customerGroupId = $customer->getGroupId();
        $customerGroupCode = '';
        $customerGroup = $this->customerGroupRepository->getById($customerGroupId);
        if ($customerGroup) $customerGroupCode = $customerGroup->getCode();
        return $customerGroupCode;
    }

    public function getLocale(CustomerInterface $customer)
    {
        $locale = $this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORES, $customer->getStoreId());
        if (empty($locale)) $locale = 'fr_FR';

        return $locale;
    }
}
