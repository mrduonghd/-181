<?php

/**
 * Mpshipping Block
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Block\Mpshipping;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Block\Product\AbstractProduct;
use Webkul\Mpshipping\Model\MpshippingmethodFactory;
use Magento\Directory\Model\ResourceModel\Country;
use Webkul\Mpshipping\Model\MpshippingFactory;

class Mpshipping extends AbstractProduct
{
    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $_urlHelper;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_session;
    /**
     * @var Webkul\Mpshipping\Model\MpshippingmethodFactory
     */
    protected $_mpshippingMethod;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;
    /**
     * @var Webkul\Mpshipping\Model\MpshippingFactory
     */
    protected $_mpshippingModel;
    /**
     * @var Webkul\Mpshipping\Helper\Data
     */
    protected $_mpshippingHelperData;
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelperData;

    /**
     * @param \Magento\Catalog\Block\Product\Context    $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Framework\Url\Helper\Data        $urlHelper
     * @param Customer                                  $customer
     * @param \Magento\Customer\Model\Session           $session
     * @param MpshippingmethodFactory                   $shippingmethodFactory
     * @param Country\CollectionFactory                 $countryCollectionFactory
     * @param MpshippingFactory                         $mpshippingModel
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        Customer $customer,
        \Magento\Customer\Model\Session $session,
        MpshippingmethodFactory $shippingmethodFactory,
        Country\CollectionFactory $countryCollectionFactory,
        MpshippingFactory $mpshippingModel,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Webkul\Mpshipping\Helper\Data $mpshippingHelperData,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Marketplace\Helper\Data $mpHelperData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_postDataHelper = $postDataHelper;
        $this->_urlHelper = $urlHelper;
        $this->_customer = $customer;
        $this->_session = $session;
        $this->_mpshippingMethod = $shippingmethodFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_mpshippingModel = $mpshippingModel;
        $this->_localeCurrency = $localeCurrency;
        $this->_jsonHelper = $jsonHelper;
        $this->_mpshippingHelperData = $mpshippingHelperData;
        $this->_mpHelperData = $mpHelperData;
    }
    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_mpshippingHelperData->getPartnerId();
    }
    /**
     * @param  int $partnerId
     * @return \Webkul\Mpshipping\Model\Mpshipping
     */
    public function getShippingCollection($partnerId = null)
    {
        $querydata = $this->_mpshippingModel->create()
            ->getCollection()
            ->addFieldToFilter(
                'partner_id',
                ['eq' => $partnerId]
            );
        return $querydata;
    }

    public function getShippingMethodCollection()
    {
        $shippingMethodCollection = $this->_mpshippingMethod
            ->create()
            ->getCollection();
        return $shippingMethodCollection;
    }

    public function getShippingforShippingMethod($methodId, $partnerId)
    {
        $querydata = $this->_mpshippingModel
            ->create()
            ->getCollection()
            ->addFieldToFilter(
                'shipping_method_id',
                ['eq' => $methodId]
            )
            ->addFieldToFilter(
                'partner_id',
                ['eq' => $partnerId]
            );
        return $querydata;
    }

    public function getShippingMethodName($shippingMethodId)
    {
        $methodName = '';
        $shippingMethodModel = $this->_mpshippingMethod->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $shippingMethodId);
        foreach ($shippingMethodModel as $shippingMethod) {
            $methodName = $shippingMethod->getMethodName();
        }
        return $methodName;
    }
    public function getCountryOptionArray()
    {
        $options = $this->getCountryCollection()
            ->setForegroundCountries($this->getTopDestinations())
            ->toOptionArray();
        $options[0]['label'] = 'Please select Country';

        return $options;
    }
    public function getCountryCollection()
    {
        $collection = $this->_countryCollectionFactory
            ->create()
            ->loadByStore();
        return $collection;
    }
    /**
     * Retrieve list of top destinations countries.
     *
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string) $this->_scopeConfig->getValue(
            'general/country/destinations',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }

    public function getBaseCurrencySymbol()
    {
        $currencycode = $this->_storeManager->getStore()->getBaseCurrencyCode();
        $currency = $this->_localeCurrency->getCurrency($currencycode);
        return $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
    }

    /**
     * [Get Shipping Data Helper]
     */
    public function getHelperData()
    {
        return $this->_mpshippingHelperData;
    }
    
    /**
     * [Get Json Helper]
     */
    public function getJsonHelper()
    {
        return $this->_jsonHelper;
    }

    /**
     * [Mp HelperData]
     */
    public function getMpHelperData()
    {
        return $this->_mpHelperData;
    }
}
