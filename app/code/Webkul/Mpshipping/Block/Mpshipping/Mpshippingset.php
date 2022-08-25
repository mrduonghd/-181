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
use Webkul\Mpshipping\Model\MpshippingsetFactory;

class Mpshippingset extends AbstractProduct
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
     * @var Webkul\Mpshipping\Model\MpshippingsetFactory
     */
    protected $_mpshippingsetModel;
    /**
     * @var Webkul\Mpshipping\Helper\Data
     */
    protected $_mpshippingHelperData;

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
        MpshippingsetFactory $mpshippingsetModel,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Webkul\Mpshipping\Helper\Data $mpshippingHelperData,
        \Webkul\Marketplace\Helper\Data $mpHelperData,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_postDataHelper = $postDataHelper;
        $this->_urlHelper = $urlHelper;
        $this->_customer = $customer;
        $this->_session = $session;
        $this->_mpshippingMethod = $shippingmethodFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_mpshippingsetModel = $mpshippingsetModel;
        $this->_localeCurrency = $localeCurrency;
        $this->_priceCurrency = $priceCurrency;
        $this->_mpshippingHelperData = $mpshippingHelperData;
        $this->_mpHelperData = $mpHelperData;
        $this->_jsonHelper = $jsonHelper;
    }
    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_mpshippingHelperData->getPartnerId();
    }

    public function getShippingSetCollection()
    {
        $paramData = $this->getRequest()->getParams();
        $filter = '';
        $filterType = '';
        $filterPriceFrom = '';
        $filterPriceTo = '';
        if (isset($paramData['s'])) {
            $filter = $paramData['s'] != '' ? $paramData['s'] : '';
        }
        if (isset($paramData['type'])) {
            $filterType = $paramData['type'] != '' ? $paramData['type'] : '';
        }
        if (isset($paramData['price_from'])) {
            $filterPriceFrom = $paramData['price_from'] != '' ? $paramData['price_from'] : '';
        }
        if (isset($paramData['price_to'])) {
            $filterPriceTo = $paramData['price_to'] != '' ? $paramData['price_to'] : '';
        }
        if ($filter) {
            $methodId = $this->getMethodIdByName($filter);
        }
        $partnerId = $this->getCustomerId();
        $shippingSetCollection = $this->_mpshippingsetModel
            ->create()
            ->getCollection()
            ->addFieldToFilter('partner_id', ['eq'=>$partnerId]);
        if ($filter) {
            $shippingSetCollection->addFieldToFilter('shipping_method_id', ['eq',$methodId]);
        }
        if ($filterType) {
            $shippingSetCollection->addFieldToFilter('shipping_type', ['eq',$filterType]);
        }
        if ($filterPriceFrom) {
            $shippingSetCollection->addFieldToFilter('price_from', ['gteq',$filterPriceFrom]);
        }
        if ($filterPriceTo) {
            $shippingSetCollection->addFieldToFilter('price_to', ['lteq',$filterPriceTo]);
        }
        return $shippingSetCollection;
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
    public function getMethodIdByName($methodName)
    {
        $methodId= 0;
        $shippingMethodModel = $this->_mpshippingMethod->create()
                                  ->getCollection()
                                  ->addFieldToFilter('method_name', ['eq'=>$methodName]);
        foreach ($shippingMethodModel as $shippingMethod) {
            $methodId = $shippingMethod->getEntityId();
        }
        return $methodId;
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
     * [Mp HelperData]
     */
    public function getMpHelperData()
    {
        return $this->_mpHelperData;
    }

    /**
     * [Get Request IsSecure]
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }

    /**
     * [Get Request Params]
     */
    public function getParams()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * [Get Json Helper]
     */
    public function getJsonHelper()
    {
        return $this->_jsonHelper;
    }
}
