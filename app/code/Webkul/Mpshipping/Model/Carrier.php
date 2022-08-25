<?php
/**
 * Mpshipping
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Model;

use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Webkul\Mpshipping\Model\MpshippingmethodFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Session\SessionManager;
use Magento\Quote\Model\Quote\Item\OptionFactory;
use Webkul\Mpshipping\Model\MpshippingFactory;
use Webkul\Mpshipping\Model\MpshippingsetFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Webkul\MarketplaceBaseShipping\Model\ShippingSettingRepository;
use Webkul\Mpshipping\Helper\Data as HelperData;
use Webkul\Mpshipping\Model\MpshippingDistFactory;
use Magento\Framework\HTTP\Client\Curl;

class Carrier extends \Webkul\MarketplaceBaseShipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Code of the carrier.
     *
     * @var string
     */
    const CODE = 'webkulshipping';
    /**
     * Code of the carrier.
     *
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * Rate request data.
     *
     * @var \Magento\Quote\Model\Quote\Address\RateRequest|null
     */
    protected $_request = null;

    /**
     * Rate result data.
     *
     * @var Result|null
     */
    protected $_result = null;
    /**
     * @var SessionManager
     */
    protected $_coreSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;
    /**
     * Raw rate request data
     *
     * @var \Magento\Framework\DataObject|null
     */
    protected $_rawRequest = null;

    /**
     * Raw rate request data
     *
     * @var \Magento\Framework\DataObject|null
     */
    protected $baseRequest = null;
    /**
     * Raw rate request data
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestInterface = null;

    protected $_isFixed = true;
    /**
     * @var Webkul\Mpshipping\Model\MpshippingmethodFactory
     */
    protected $_mpshippingMethod;
    /**
     * @var MpshippingFactory
     */
    protected $_mpShippingModel;
    /**
     * @var MpshippingsetFactory
     */
    protected $_mpShippingsetModel;
    /**
     * @var MpshippingDistFactory
     */
    protected $mpshippingDistModel;
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param SessionManager                                              $coreSession
     * @param \Magento\Checkout\Model\Session                             $checkoutSession
     * @param \Magento\Customer\Model\Session                             $customerSession
     * @param MpshippingmethodFactory                                     $shippingmethodFactory
     * @param MpshippingFactory                                           $mpshippingModel
     * @param MpshippingsetFactory                                        $mpshippingsetModel
     * @param MpshippingDistFactory                                       $mpshippingDistModel
     * @param array                                                       $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        SessionManager $coreSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\RequestInterface $requestInterface,
        PriceCurrencyInterface $priceCurrency,
        OptionFactory $optionFactory,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        ProductFactory $productFactory,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        ShippingSettingRepository $shippingSettingRepository,
        MpshippingmethodFactory $shippingmethodFactory,
        MpshippingFactory $mpshippingModel,
        MpshippingsetFactory $mpshippingsetModel,
        HelperData $helperData,
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface,
        MpshippingDistFactory $mpshippingDistModel,
        Curl $curl,
        \Magento\Framework\Escaper $escaper,
        \Webkul\Mpshipping\Model\SellerLocationFactory $sellerLocation,
        array $data = []
    ) {
        $this->_mpshippingMethod = $shippingmethodFactory;
        $this->_mpShippingModel = $mpshippingModel;
        $this->_mpShippingsetModel = $mpshippingsetModel;
        $this->mpshippingDistModel = $mpshippingDistModel;
        $this->helperData = $helperData;
        $this->curl = $curl;
        $this->escaper = $escaper;
        $this->sellerLocation = $sellerLocation;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $rateResultFactory,
            $rateMethodFactory,
            $regionFactory,
            $coreSession,
            $checkoutSession,
            $customerSession,
            $currencyFactory,
            $storeManager,
            $localeFormat,
            $jsonHelper,
            $requestInterface,
            $priceCurrency,
            $optionFactory,
            $customerFactory,
            $addressFactory,
            $mpProductFactory,
            $productFactory,
            $saleslistFactory,
            $shippingSettingRepository,
            $data,
            $serializerInterface
        );
    }
    /**
     * Collect and get rates.
     *
     * @param RateRequest $request
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Error|bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        $this->baseRequest = $request;
        if (!$this->getConfigFlag('active') ||
          $this->helperData->isMultiShippingActive() ||
          $this->_scopeConfig->getValue('carriers/wkpickup/active')
        ) {
            return false;
        }
        $this->setRequest($request);
        $shippingpricedetail = $this->getShippingPricedetail($this->_rawRequest);
        return $shippingpricedetail;
    }

    /**
     * Prepare and set request to this instance.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setRequest(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        parent::setRequest($request);
        if ($request->getDestRegionId()) {
            $regionId = $request->getDestRegionId();
        } else {
            $regionId = '*';
        }
        $request->setDestRegionId($regionId);
        if ($this->helperData->getDistanceShippingStatus()) {
            $shippingDetails = $request->getShippingDetails();
            foreach ($shippingDetails as $key => $shippingDetail) {
                $sellerId = $shippingDetail['seller_id'];
                if ($sellerId) {
                    $sellerAddress = $this->sellerLocation->create()
                                        ->getCollection()
                                        ->addFieldToFilter('partner_id', $sellerId)
                                        ->getFirstItem();
                    $latitude = $sellerAddress->getLatitude();
                    $longitude = $sellerAddress->getLongitude();
                    $status = ($latitude && $longitude) ? 1 : 0;
                    $shippingDetail['dist_ship_status'] = $status;
                    $shippingDetail['dist_latitude'] = $latitude;
                    $shippingDetail['dist_longitude'] = $longitude;
                } else {
                    $latitude = $this->getConfigData('latitude');
                    $longitude = $this->getConfigData('longitude');
                    $status = ($latitude && $longitude) ? 1 : 0;
                    $shippingDetail['dist_ship_status'] = $status;
                    $shippingDetail['dist_latitude'] = $latitude;
                    $shippingDetail['dist_longitude'] = $longitude;
                }
                $shippingDetails[$key] = $shippingDetail;
            }
            $request->setShippingDetails($shippingDetails);
        }
    }
    /**
     * Calculate the rate according to Tabel Rate shipping defined by the sellers.
     *
     * @return Result
     */
    public function getShippingPricedetail(RateRequest $request)
    {
        $requestData = $request;
        $submethod = [];
        $shippinginfo = [];
        $msg = '';
        $msgArray = [];
        $handling = 0;
        $totalPriceArr = [];
        $priceArr = [];
        $flag = false;
        $check = false;
        $returnError = false;
        $storePickupStatus = false;
        foreach ($requestData->getShippingDetails() as $shipdetail) {
            $thisMsg = false;
            $priceArr = [];
            $price = 0;
            /*Calculate price itemwise for seller store pickup*/
            $itemPriceDetails = [];
            $items = explode(',', $shipdetail['item_id']);
            $newShipderails = [];
            $newShipderails['seller_id'] = $shipdetail['seller_id'];
            $newShipderails['total_amount'] = $shipdetail['total_amount'];
            $newShipderails['product_name'] = $shipdetail['product_name'];
            $priceArr = $this->getShippingPriceRates($shipdetail, $requestData);
            if (isset($shipdetail['item_weight_details']) &&
                isset($shipdetail['item_product_price_details']) &&
                isset($shipdetail['item_qty_details'])
                ) {
                $storePickupStatus = true;
                $itemPriceArr = [];
                $itemCostArr = [];
                foreach ($items as $itemId) {
                    $newShipderails['items_weight'] = $shipdetail['item_weight_details'][$itemId];
                    $newShipderails['price'] = $shipdetail['item_product_price_details'][$itemId]
                    * $shipdetail['item_qty_details'][$itemId];
                    $priceArr = $this->getShippingPriceRates($newShipderails, $requestData);
                    $itemPriceArrWithMethodId = $this->getSubMethodForItemPrice($priceArr);
                    $itemPriceDetails[$itemId] = $itemPriceArrWithMethodId;
                }
            }
            /*End seller store pickup*/
            if (empty($priceArr)) {
                list($msg, $msgArray) = $this->getErrorMsg($msgArray, $shipdetail, false);
            }
            if ($this->helperData->isMultiShippingActive() || $storePickupStatus) {
                if (empty($priceArr)) {
                    $totalPriceArr = [];
                    $flag = true;
                    $debugData['result'] = ['error' => 1, 'errormsg'=>$msg];
                    return [];
                }
            } else {
                if (!empty($totalPriceArr)) {
                    foreach ($priceArr as $method => $price) {
                        if (array_key_exists($method, $totalPriceArr)) {
                            $check = true;
                            $totalPriceArr[$method] = $totalPriceArr[$method] + $priceArr[$method];
                        } else {
                            $thisMsg = true;
                            unset($priceArr[$method]);
                        }
                        $flag = $check == true ? false : true;
                    }
                } else {
                    $totalPriceArr = $priceArr;
                }
            }
            if (!empty($priceArr)) {
                foreach ($totalPriceArr as $method => $price) {
                    if (!array_key_exists($method, $priceArr)) {
                        unset($totalPriceArr[$method]);
                    }
                }
            } else {
                $totalPriceArr = [];
                $flag = true;
            }
            if ($flag) {
                if ($thisMsg) {
                    list($msg, $msgArray) = $this->getErrorMsg($msgArray, $shipdetail, true);
                }
                $returnError = true;
                $debugData['result'] = ['error' => 1, 'errormsg'=>$msg];
            }
            $submethod = $this->getSubMethodsForRate($priceArr);
            $handling = $handling + $price;
            if (!isset($shipdetail['item_id_details'])) {
                $shipdetail['item_id_details'] = [];
            }
            if (!isset($shipdetail['item_name_details'])) {
                $shipdetail['item_name_details'] = [];
            }
            if (!isset($shipdetail['item_qty_details'])) {
                $shipdetail['item_qty_details'] = [];
            }
            array_push(
                $shippinginfo,
                [
                    'seller_id' => $shipdetail['seller_id'],
                    'methodcode' => $this->_code,
                    'shipping_ammount' => $price,
                    'product_name' => $shipdetail['product_name'],
                    'submethod' => $submethod,
                    'item_ids' => $shipdetail['item_id'],
                    'item_price_details' => $itemPriceDetails,
                    'item_id_details' => $shipdetail['item_id_details'],
                    'item_name_details' => $shipdetail['item_name_details'],
                    'item_qty_details' => $shipdetail['item_qty_details']
                ]
            );
        }
        if ($returnError) {
            if ($this->helperData->isMultiShippingActive() || $storePickupStatus) {
                return $debugData;
            }
            return $this->_parseXmlResponse($debugData);
        }
        $totalpric = ['totalprice' => $totalPriceArr, 'costarr' => $priceArr];
        $result = ['handlingfee' => $totalpric, 'shippinginfo' => $shippinginfo, 'error' => 0];
        $shippingAll = $this->_coreSession->getShippingInfo();
        $shippingAll[$this->_code] = $result['shippinginfo'];
        $this->_coreSession->setShippingInfo($shippingAll);
        if ($this->helperData->isMultiShippingActive() || $storePickupStatus) {
            return $result;
        }
        return $this->_parseXmlResponse($totalpric);
    }
    /**
     * [getAllowedMethods]
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['webkulshipping' => $this->_scopeConfig->getValue('carriers/webkulshipping/title')];
    }
    /**
     * [getShipMethodNameById get method name by method Id]
     * @param  int $shipMethodId [Method Id]
     * @return string $methodName   [Method Name]
     */
    public function getShipMethodNameById($shipMethodId)
    {
        $methodName = '';
        $shippingMethodModel = $this->_mpshippingMethod->create()
            ->load($shipMethodId);
        $methodName = $shippingMethodModel->getMethodName();
        $methodName = $this->escaper->escapeHtml($methodName);
        return $methodName;
    }
    /**
     * [_parseXmlResponse set Shipping result]
     * @param  array $response
     * @return \Magento\Shipping\Model\Rate\ResultFactory $result
     */
    protected function _parseXmlResponse($response)
    {
        $result = $this->_rateResultFactory->create();
        if (array_key_exists('result', $response) && $response['result']['error'] !== '') {
            $this->_errors[$this->_code] = $response['result']['errormsg'];
            $errors = explode('<br>', $response['result']['errormsg']);
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            foreach ($errors as $key => $value) {
                $errorMsg = $value;
            }
            $error->setErrorMessage($errorMsg);
            return $error;
            // Display error message if there
        } else {
            $totalPriceArr = $response['totalprice'];
            $costArr = $response['costarr'];
            foreach ($totalPriceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier($this->_code);
                $rate->setCarrierTitle($this->getConfigData('title'));
                $methodCode = $this->getMethodCodeByName($method);
                $rate->setMethod($methodCode);
                $rate->setMethodTitle($method);
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }
        return $result;
    }
    /**
     * [getMethodCodeByName get methodId by the Method name]
     * @param  string $methodName [Method Name]
     * @return int $methodId      [Method Id]
     */
    public function getMethodCodeByName($methodName)
    {
        $methodId = 0;
        $methodName = htmlentities($methodName);
        $shippingMethodModel = $this->_mpshippingMethod->create()->getCollection()
                             ->addFieldToFilter('method_name', ['eq'=>$methodName]);
        foreach ($shippingMethodModel as $method) {
            $methodId = $method->getEntityId();
        }
        return $methodId;
    }
    /**
     * [getShippingcollectionAccordingToDetails Calculate shipping according to Postal Code]
     * @param  string $countryId
     * @param  int $sellerId
     * @param  string $regionId
     * @param  int $postalCode
     * @param  float $weight
     * @return \Webkul\Mpshipping\Model\Mpshipping $shipping
     */
    public function getShippingcollectionAccordingToDetails($countryId, $sellerId, $regionId, $postalCode, $weight)
    {

        $shipping = $this->_mpShippingModel->create()
                ->getCollection()
                ->addFieldToFilter('dest_country_id', ['eq' => $countryId])
                ->addFieldToFilter('partner_id', ['eq' => $sellerId])
                ->addFieldToFilter(
                    ['dest_region_id','dest_region_id'],
                    [
                          ['eq'=>$regionId],
                          ['eq'=>'*']
                      ]
                )
                ->addFieldToFilter(
                    ['dest_zip','dest_zip'],
                    [
                          ['lteq'=>$postalCode],
                          ['eq'=>'*']
                      ]
                )
                ->addFieldToFilter(
                    ['dest_zip_to','dest_zip_to'],
                    [
                          ['gteq'=>$postalCode],
                          ['eq'=>'*']
                      ]
                )
                ->addFieldToFilter('weight_from', ['lteq' => $weight])
                ->addFieldToFilter('weight_to', ['gteq' => $weight]);
        return $shipping;
    }
    /**
     * [getShippingcollectionForAlphaNumericZipcode Calculate shipping for AlphanumericZipcode]
     * @param  string $countryId
     * @param  int $sellerId
     * @param  string $regionId
     * @param  string $postalCode
     * @param  float $weight
     * @return \Webkul\Mpshipping\Model\Mpshipping $shipping
     */
    public function getShippingcollectionForAlphaNumericZipcode($countryId, $sellerId, $regionId, $postalCode, $weight)
    {
        $shipping = $this->_mpShippingModel->create()
                  ->getCollection()
                  ->addFieldToFilter('dest_country_id', ['eq' => $countryId])
                  ->addFieldToFilter('partner_id', ['eq' => $sellerId])
                ->addFieldToFilter(
                    ['dest_region_id','dest_region_id'],
                    [
                            ['eq'=>$regionId],
                            ['eq'=>'*']
                        ]
                )
                  ->addFieldToFilter('zipcode', ['eq' => $postalCode])
                  ->addFieldToFilter('weight_from', ['lteq' => $weight])
                  ->addFieldToFilter('weight_to', ['gteq' => $weight]);
        return $shipping;
    }
    /**
     * [getShippingcollectionAccordingastrik Calculate shipping for Astrik Postal code]
     * @param  string $countryId
     * @param  int $sellerId
     * @param  string $regionId
     * @param  string $postalCode
     * @param  float $weight
     * @return \Webkul\Mpshipping\Model\Mpshipping $shipping
     */
    public function getShippingcollectionAccordingastrik($countryId, $sellerId, $regionId, $postalCode, $weight)
    {
        $shipping = $this->_mpShippingModel->create()
                ->getCollection()
                ->addFieldToFilter('dest_country_id', ['eq' => $countryId])
                ->addFieldToFilter('partner_id', ['eq' => $sellerId])
                ->addFieldToFilter(
                    ['dest_region_id','dest_region_id'],
                    [
                          ['eq'=>$regionId],
                          ['eq'=>'*']
                      ]
                )
                ->addFieldToFilter('dest_zip', ['eq' => $postalCode])
                ->addFieldToFilter('dest_zip_to', ['eq' => $postalCode])
                ->addFieldToFilter('weight_from', ['lteq' => $weight])
                ->addFieldToFilter('weight_to', ['gteq' => $weight]);
        return $shipping;
    }
    /**
     * [getSuperShippingPrice Calculate shipping from seller's super sets]
     * @param  array $shipdetail
     * @return \Webkul\Mpshipping\Model\Mpshippingset $shipping
     */
    public function getSuperShippingPrice($shipdetail)
    {
        $shipping = $this->_mpShippingsetModel->create()
              ->getCollection()
              ->addFieldToFilter('partner_id', ['eq'=>$shipdetail['seller_id']])
                      ->addFieldToFilter('price_from', ['lteq'=>$shipdetail['total_amount']])
                  ->addFieldToFilter('price_to', ['gteq'=>$shipdetail['total_amount']]);
        return $shipping;
    }
    /**
     * [getAdminSuperShippingPrice Calculate shipping from admin's super sets]
     * @param  array $shipdetail
     * @return \Webkul\Mpshipping\Model\Mpshippingset $shipping
     */
    public function getAdminSuperShippingPrice($shipdetail)
    {
        $shipping = $this->_mpShippingsetModel->create()
              ->getCollection()
              ->addFieldToFilter('partner_id', ['eq'=>0])
                      ->addFieldToFilter('price_from', ['lteq'=>$shipdetail['total_amount']])
                  ->addFieldToFilter('price_to', ['gteq'=>$shipdetail['total_amount']]);
        return $shipping;
    }
    /**
     * [getShippingPriceRates Calculate Shipping for admin and seller]
     * @param  array $shipdetail
     * @param  RateRequest $requestData
     * @return object $shipping
     */
    public function getShippingPriceRates($shipdetail, $requestData)
    {
        $priceArr = [];
        $shipping = $this->getSuperShippingPrice($shipdetail);
        $priceArr = $this->getPriceArrForRate($shipping, $priceArr);
        if ($shipping->getSize() == 0) {
            if (array_key_exists('items_weight', $shipdetail)) {
                if (!is_numeric($requestData->getDestPostal())) {
                    $shipping = $this->shippingForAlphanumericZipcode($shipdetail, $requestData);
                } else {
                    $shipping = $this->shippingForNumericZipcode($shipdetail, $requestData);
                }
                $priceArr = $this->getPriceArrForRate($shipping, $priceArr);
            }
            if ($this->helperData->getDistanceShippingStatus() &&
                (isset($shipdetail['dist_ship_status']) &&
                $shipdetail['dist_ship_status'])
            ) {
                $from = [
                    'latitude' =>$shipdetail['dist_latitude'],
                    'longitude' =>$shipdetail['dist_longitude']
                ];
                $destCity = $requestData->getDestCity();
                $destPostcode = $requestData->getDestPostcode();
                $destCountry = $requestData->getDestCountryId();
                $destAddress = $destCity."+".$destPostcode."+".$destCountry;
                $to = $this->getLocation($destAddress);
                if ($to['latitude'] && $to['longitude']) {
                    $distance = $this->helperData->getDistanceFromTwoPoints($from, $to);
                    $distShipping = $this->shippingByDistance($shipdetail, $distance);
                    $priceArr = $this->getPriceArrForRate($distShipping, $priceArr);
                }
            }

        }
        return $priceArr;
    }
    /**
     * shippingByDistance function
     *
     * @param mixed $shipdetail
     * @param float $distance
     * @return void
     */
    protected function shippingByDistance($shipdetail, $distance)
    {
        $distShipping = $this->mpshippingDistModel->create()
                            ->getCollection()
                            ->addFieldToFilter('partner_id', $shipdetail['seller_id'])
                            ->addFieldToFilter('price_from', ['lteq'=>$shipdetail['total_amount']])
                            ->addFieldToFilter('price_to', ['gteq'=>$shipdetail['total_amount']])
                            ->addFieldToFilter('dist_from', ['lteq'=>$distance])
                            ->addFieldToFilter('dist_to', ['gteq'=>$distance]);
        return $distShipping;
    }
    /**
     * [shippingForNumericZipcode Calculate shipping for the numeric zipcode]
     * @param  array $shipdetail
     * @param  RateRequest $requestData
     * @return \Webkul\Mpshipping\Model\Mpshipping $shipping
     */
    public function shippingForNumericZipcode($shipdetail, $requestData)
    {
        $shipping = $this->getShippingcollectionAccordingToDetails(
            $requestData->getDestCountryId(),
            $shipdetail['seller_id'],
            $requestData->getDestRegionId(),
            (int)($requestData->getDestPostal()),
            $shipdetail['items_weight']
        );
        if ($shipping->getSize() == 0) {
            $shipping = $this->getShippingcollectionAccordingastrik(
                $requestData->getDestCountryId(),
                $shipdetail['seller_id'],
                $requestData->getDestRegionId(),
                '*',
                $shipdetail['items_weight']
            );
        }
        if ($shipping->getSize() == 0) {
            if ($this->getConfigData('allowadmin')) {
                $price = 0;
                $shipping = $this->getAdminSuperShippingPrice($shipdetail);
                if ($shipping->getSize() == 0) {
                    $shipping = $this->getShippingcollectionAccordingToDetails(
                        $requestData->getDestCountryId(),
                        0,
                        $requestData->getDestRegionId(),
                        (int)($requestData->getDestPostal()),
                        $shipdetail['items_weight']
                    );
                    if ($shipping->getSize() == 0) {
                        $shipping = $this->getShippingcollectionAccordingastrik(
                            $requestData->getDestCountryId(),
                            0,
                            $requestData->getDestRegionId(),
                            '*',
                            $shipdetail['items_weight']
                        );
                    }
                }
            }
        }
        return $shipping;
    }
    /**
     * [shippingForAlphanumericZipcode Calculate shipping for AlphanumericZipcode]
     * @param  array $shipdetail
     * @param  RateRequest $requestData
     * @return \Webkul\Mpshipping\Model\Mpshipping $shipping
     */
    public function shippingForAlphanumericZipcode($shipdetail, $requestData)
    {
        $shipping= $this->getShippingcollectionForAlphaNumericZipcode(
            $requestData->getDestCountryId(),
            $shipdetail['seller_id'],
            $requestData->getDestRegionId(),
            $requestData->getDestPostal(),
            $shipdetail['items_weight']
        );
        if ($shipping->getSize() == 0) {
            $shipping = $this->getShippingcollectionForAlphaNumericZipcode(
                $requestData->getDestCountryId(),
                $shipdetail['seller_id'],
                $requestData->getDestRegionId(),
                '*',
                $shipdetail['items_weight']
            );
        }
        if ($shipping->getSize() == 0) {
            if ($this->getConfigData('allowadmin')) {
                $shipping = $this->getAdminSuperShippingPrice($shipdetail);
                if ($shipping->getSize() == 0) {
                    $shipping= $this->getShippingcollectionForAlphaNumericZipcode(
                        $requestData->getDestCountryId(),
                        0,
                        $requestData->getDestRegionId(),
                        $requestData->getDestPostal(),
                        $shipdetail['items_weight']
                    );
                }
                if ($shipping->getSize() == 0) {
                    $shipping = $this->getShippingcollectionForAlphaNumericZipcode(
                        $requestData->getDestCountryId(),
                        0,
                        $requestData->getDestRegionId(),
                        '*',
                        $shipdetail['items_weight']
                    );
                }
            }
        }
        return $shipping;
    }
    /**
     * [getPriceArrForRate return shipping price with shipping method name from the shipping data]
     * @param  object $shipping
     * @return array
     */
    public function getPriceArrForRate($shipping, $priceArr = [])
    {
        foreach ($shipping as $ship) {
            $price = floatval($ship->getPrice());
            $shipMethodId = $ship->getShippingMethodId();
            if ($shipMethodId) {
                $shipMethodName = $this->getShipMethodNameById($shipMethodId);
            } else {
                $shipMethodName = $this->getConfigData('title');
            }
            $priceArr[$shipMethodName] = $price;
        }
        return $priceArr;
    }
    /**
     * [getSubMethodForItemPrice returns submethods arrays with method code for each Item]
     * @param  array $priceArr
     * @return array
     */
    public function getSubMethodForItemPrice($priceArr)
    {
        $submethod = [];
        if (!empty($priceArr)) {
            foreach ($priceArr as $name => $value) {
                $methodCode = $this->getMethodCodeByName($name);
                $submethod[$methodCode] = $value;
            }
        }
        return $submethod;
    }
    /**
     * [getSubMethodsForRate returns submethods arrays with method code]
     * @param  array $priceArr
     * @return array
     */
    public function getSubMethodsForRate($priceArr)
    {
        $submethod = [];
        if (!empty($priceArr)) {
            foreach ($priceArr as $index => $price) {
                $methodCode = $this->getMethodCodeByName($index);
                $submethod[$methodCode] = [
                    'method' => $index.' ('.$this->getConfigData('title').')',
                    'cost' => $price,
                    'base_amount' => $price,
                    'error' => 0,
                ];
            }
        }
        return $submethod;
    }

    /**
     * [getErrorMsg returns array of custom error messages]
     * @param  array $msgArray
     * @param  array $shipdetail
     * @param  boolean $status
     * @return array
     */
    public function getErrorMsg($msgArray, $shipdetail, $status)
    {
        $productMsg = '';
        $msgArray[] = $shipdetail['product_name'];
        if (!$status) {
            foreach ($msgArray as $key => $product) {
                if ($productMsg=='') {
                    $productMsg = $product;
                } else {
                    $productMsg = $productMsg.", ".$product;
                }
            }
            $msg = __("Seller Of Product(s) %1 do not provide shipping service at your location.", $productMsg);
        } else {
            $msg = __("You can not buy these products together with this shipping method, you can buy separately!");
        }
        return [$msg, $msgArray];
    }

    /**
     * getLocation
     * @param string $address
     * @return array
     */
    private function getLocation($address)
    {
        try {
            $address = str_replace(' ', '+', $address);
            $address = str_replace('++', '+', $address);
            $apiKey = $this->helperData->getGoogleApiKey();
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&sensor=false&key='.$apiKey;
            $this->curl->get($url);
            $response = $this->jsonHelper->jsonDecode($this->curl->getBody());
            $location = $response['results'][0]['geometry']['location'];
            return ['latitude' => $location['lat'], 'longitude' => $location['lng']];
        } catch (\Exception $e) {
            return ['latitude' => '', 'longitude' => ''];
        }
    }
}
