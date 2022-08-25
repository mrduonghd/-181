<?php
/**
 * Mpshipping Helper
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Helper;

use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Magento\Framework\Encryption\EncryptorInterface;
use Webkul\Mpshipping\Model\SellerLocationFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var SellerCollection
     */
    protected $_sellerCollection;

    /**
     * @var CustomerCollection
     */
    protected $_customerCollection;
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_marketplaceHelperData;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;
    /**
     * @var SellerLocationFactory
     */
    protected $sellerLocation;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session       $customerSession
     * @param EncryptorInterface $encryptor
     * @param SellerLocationFactory $sellerLocation
     */

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        SellerCollection $sellerCollectionFactory,
        CustomerCollection $customerCollectionFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelperData,
        \Magento\Customer\Model\Session $customerSession,
        EncryptorInterface $encryptor = null,
        SellerLocationFactory $sellerLocation = null
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_customerCollection = $customerCollectionFactory;
        $this->_sellerCollection = $sellerCollectionFactory;
        $this->_marketplaceHelperData = $marketplaceHelperData;
        $this->_customerSession = $customerSession;
        $this->encryptor = $encryptor ?: \Magento\Framework\App\ObjectManager::getInstance()
                                                ->create(EncryptorInterface::class);
        $this->sellerLocation = $sellerLocation ?: \Magento\Framework\App\ObjectManager::getInstance()
                                                ->create(SellerLocationFactory::class);
    }

    /**
     * get shipping is enabled or not for system config.
     */
    public function getMpshippingEnabled()
    {
        return $this->_scopeConfig->getValue(
            'carriers/webkulshipping/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
     /**
      * get multi shipping is enabled or not for system config.
      */
    public function getMpmultishippingEnabled()
    {
        return $this->_scopeConfig->getValue(
            'carriers/mp_multi_shipping/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get table rate shipping title from system config.
     */
    public function getMpshippingTitle()
    {
        return $this->_scopeConfig->getValue(
            'carriers/webkulshipping/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get table rate shipping name from system config.
     */
    public function getMpshippingName()
    {
        return $this->_scopeConfig->getValue(
            'carriers/webkulshipping/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get table rate shipping allow admin settings from system config.
     */
    public function getMpshippingAllowadmin()
    {
        return $this->_scopeConfig->getValue(
            'carriers/webkulshipping/allowadmin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get customer id from customer session.
     */
    public function getPartnerId()
    {
        $partnerId = $this->_marketplaceHelperData->getCustomerId();
        return $partnerId;
    }

    /**
     * Get List of Seller Ids
     *
     * @return array
     */
    public function getSellerIdList()
    {
        $sellerIdList = [];
        $collection = $this->_sellerCollection
                            ->create()
                            ->addFieldToFilter('is_seller', 1);
        foreach ($collection as $item) {
            $sellerIdList[] = $item->getSellerId();
        }
        return $sellerIdList;
    }

    /**
     * Get List of Sellers
     *
     * @return array
     */
    public function getSellerList()
    {
        $sellerIdList = $this->getSellerIdList();
        $sellerList = ['0' => 'Admin'];
        $collection = $this->_customerCollection
                            ->create()
                            ->addAttributeToSelect('firstname')
                            ->addAttributeToSelect('lastname')
                            ->addFieldToFilter('entity_id', ['in' => $sellerIdList]);
        foreach ($collection as $item) {
            $sellerList[$item->getId()] = $item->getFirstname().' '.$item->getLastname();
        }
        return $sellerList;
    }

    /**
     * isMultiShippingActive
     */
    public function isMultiShippingActive()
    {
        if ($this->_moduleManager->isOutputEnabled("Webkul_MpMultiShipping") &&
        $this->_scopeConfig->getValue('carriers/mpmultishipping/active')) {
            return true;
        }
        return false;
    }

    /**
     * getDistanceShippingStatus function is used to get distance-wise shipping status
     *
     * @return bool
     */
    public function getDistanceShippingStatus()
    {
        return $this->_scopeConfig->getValue(
            'carriers/webkulshipping/distance',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Google API Key from Shipping Configuration
     *
     * @return string
     */
    public function getGoogleApiKey()
    {
        $encryptApiKey = $this->_scopeConfig->getValue(
            'carriers/webkulshipping/google_api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $googleApiKey = $this->encryptor->decrypt($encryptApiKey);
        return $googleApiKey;
    }
    /**
     * getDistanceUnit function
     *
     * @return int
     */
    public function getDistanceUnit()
    {
        return $this->_scopeConfig->getValue(
            'carriers/webkulshipping/distance_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * sellerShipping function
     *
     * @param int $sellerId
     * @return \Webkul\Mpshipping\Model\SellerLocation
     */
    public function sellerShipping($sellerId)
    {
        $sellerLocation =  $this->sellerLocation->create()
                           ->getCollection()
                           ->addFieldToFilter('partner_id', $sellerId)
                           ->getFirstItem();
        return $sellerLocation;
    }
    /**
     * getDistanceFromTwoPoints
     * @param mixed[string] $from
     * @param mixed[string] $to
     * @param string $radiousUnit
     * @return string $d
     */
    public function getDistanceFromTwoPoints($from, $to)
    {
        $radiousUnit = $this->getDistanceUnit();
        $R = 6371; // km
        $dLat = ($from['latitude'] - $to['latitude']) * M_PI / 180;
        $dLon = ($from['longitude'] - $to['longitude']) * M_PI / 180;
        $lat1 = $to['latitude'] * M_PI / 180;
        $lat2 = $from['latitude'] * M_PI / 180;
     
        $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $d = $R * $c;
        if ($radiousUnit) {
            $m = $d * 0.621371; //for milles
            return $m;
        }
        return $d;
    }
}
