<?php

namespace Mpx\Customer\Plugin\Model\Address\Validator;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Model\Address\AbstractAddress;

class General
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryData;

    /**
     * Construct \Mpx\Customer\Plugin\Model\Address\Validator
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Directory\Helper\Data $directoryData
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Directory\Helper\Data $directoryData
    ) {
        $this->eavConfig = $eavConfig;
        $this->directoryData = $directoryData;
    }

    /**
     * 'Custom validate customer address'
     *
     * @param \Magento\Customer\Model\Address\Validator\General $subject
     * @param \Magento\Customer\Model\Address\Validator\General $result
     * @param AbstractAddress $address
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function afterValidate(
        \Magento\Customer\Model\Address\Validator\General $subject,
        $result,
        AbstractAddress $address
    ) {
        $result = array_merge(
            $this->customRequiredFields($address),
            $this->checkOptionalFields($address)
        );

        return $result;
    }

    /**
     * Check fields that are generally required.
     *
     * @param AbstractAddress $address
     * @return array
     * @throws \Zend_Validate_Exception
     */
    public function customRequiredFields(AbstractAddress $address)
    {
        $errors = [];
        if (!\Zend_Validate::is($address->getFirstname(), 'NotEmpty')) {
            $errors[] = __('"firstname" is required. Enter and try again.');
        }

        if (!\Zend_Validate::is($address->getLastname(), 'NotEmpty')) {
            $errors[] = __('"lastname" is required. Enter and try again.');
        }

        if (!\Zend_Validate::is($address->getStreetLine(1), 'NotEmpty')) {
            $errors[] = __('"street" is required. Enter and try again.');
        }

        if (!\Zend_Validate::is($address->getCity(), 'NotEmpty')) {
            $errors[] = __('"city" is required. Enter and try again.');
        }

        return $errors;
    }

    /**
     * Check fields that are conditionally required.
     *
     * @param AbstractAddress $address
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function checkOptionalFields(AbstractAddress $address)
    {
        $this->reloadAddressAttributes($address);
        $errors = [];
        if ($this->isTelephoneRequired()
            && !\Zend_Validate::is($address->getTelephone(), 'NotEmpty')
        ) {
            $errors[] = __('"telephone" is required. Enter and try again.');
        }

        if ($this->isFaxRequired()
            && !\Zend_Validate::is($address->getFax(), 'NotEmpty')
        ) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'fax']);
        }

        if ($this->isCompanyRequired()
            && !\Zend_Validate::is($address->getCompany(), 'NotEmpty')
        ) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'company']);
        }

        $havingOptionalZip = $this->directoryData->getCountriesWithOptionalZip();
        if (!in_array($address->getCountryId(), $havingOptionalZip)
            && !\Zend_Validate::is($address->getPostcode(), 'NotEmpty')
        ) {
            $errors[] = __('"postcode" is required. Enter and try again.');
        }

        return $errors;
    }

    /**
     * Check if company field required in configuration.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isCompanyRequired()
    {
        return $this->eavConfig->getAttribute('customer_address', 'company')->getIsRequired();
    }

    /**
     * Check if telephone field required in configuration.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isTelephoneRequired()
    {
        return $this->eavConfig->getAttribute('customer_address', 'telephone')->getIsRequired();
    }

    /**
     * Check if fax field required in configuration.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isFaxRequired()
    {
        return $this->eavConfig->getAttribute('customer_address', 'fax')->getIsRequired();
    }

    /**
     * Reload address attributes for the certain store
     *
     * @param AbstractAddress $address
     * @return void
     */
    private function reloadAddressAttributes(AbstractAddress $address): void
    {
        $attributeSetId = $address->getAttributeSetId() ?: AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS;
        $address->setData('attribute_set_id', $attributeSetId);
        $this->eavConfig->getEntityAttributes(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $address);
    }
}
