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

    /**
     * @var Config
     */
    protected $eavConfig;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var GroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var ProbanceHelper
     */
    protected $helper;

    /**
     * CustomerFormater constructor.
     *
     * @param SubscriberFactory $subscriberFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param ProbanceHelper $helper
     */
    public function __construct(
        SubscriberFactory $subscriberFactory,
        AddressRepositoryInterface $addressRepository,
        Config $config,
        ScopeConfigInterface $scopeConfig,
        ProbanceHelper $helper
    )
    {
        $this->subscriberFactory = $subscriberFactory;
        $this->addressRepository = $addressRepository;
        $this->eavConfig = $config;
        $this->scopeConfig = $scopeConfig;
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
        return $datetime->format('Y-m-d H:i:s');
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
     * Get address and if exist get field else return empty string
     *
     * @param int $addressId
     * @param string $field
     * @return string
     */
    public function getFieldForAddress($addressId, $field)
    {
        try {
            if ($addressId) {
                $address = $this->addressRepository->getById($addressId);
                $method = 'get' . $this->convertToCamelCase($field);
                if (method_exists($address, $method)) {
                    return $address->$method();
                }
            }
        } catch (\Exception $e) {
        }
        return '';
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
        return $this->getFieldForAddress($addressId, 'company');
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
        return $this->getFieldForAddress($addressId, 'city');
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
        return $this->getFieldForAddress($addressId, 'country_id');
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
        try {
            if ($addressId) {
                $address = $this->addressRepository->getById($addressId);
                if ($address->getRegion()) {
                    return $address->getRegion()->getRegionCode();
                }
            }
        } catch (\Exception $e) {
        }
        return '';
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
        return $this->getFieldForAddress($addressId, 'postcode');
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
        return $this->getFieldForAddress($addressId, 'telephone');
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
        return $this->getFieldForAddress($addressId, 'company');
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
        return $this->getFieldForAddress($addressId, 'city');
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
        return $this->getFieldForAddress($addressId, 'country_id');
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
        try {
            if ($addressId) {
                $address = $this->addressRepository->getById($addressId);
                if ($address->getRegion()) {
                    return $address->getRegion()->getRegionCode();
                }
            }
        } catch (\Exception $e) {
        }
        return '';
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
        return $this->getFieldForAddress($addressId, 'postcode');
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
        return $this->getFieldForAddress($addressId, 'telephone');
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
