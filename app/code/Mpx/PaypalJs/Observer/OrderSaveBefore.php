<?php

namespace Mpx\PaypalJs\Observer;


use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Mpx\PaypalJs\Model\PaypalAuthorizationFactory;
use Mpx\PaypalJs\Model\PaypalAuthorizationInfoRepository;
use Mpx\PaypalJs\Model\ResourceModel\PaypalAuthorization\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 *Class OrderSaveAfter
 */
class OrderSaveBefore implements ObserverInterface
{
    const CODE                       = 'paypaljs';
    const INTENT_AUTHORIZE           = 'AUTHORIZE';
    const PAYPAL_AUTHORIZATION_PERIOD       = 'authorization_period';
    const PAYPAL_AUTHORIZATION_HONOR_PERIOD = 'honor_period';
    const PAYPAL_METHOD = ['paypaljs','paypalcc'];
    const PAYPAL_STATUS = [
        'Authorized'             => 'authorized',
        'AuthorizationExpired'  => 'authority_expired',
        'Captured'               => 'captured'
    ];
    const FORMAT_DATE = "Y-m-d H:i:s";


    /** @var Session */
    protected $_checkoutSession;

    /**
     * @var PaypalAuthorizationFactory
     */
    protected $paypalAuthorizationFactory;

    /** @var ScopeConfigInterface */
    protected $_scopeConfig;

    /**
     * @var DateTime
     */
    protected $time;

    /**
     * @var PaypalAuthorizationInfoRepository
     */
    protected $paypalAuthorizationInfoRepository;

    protected $collectionFactory;

    /**
     * @param PaypalAuthorizationFactory $paypalAuthorizationFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param PaypalAuthorizationInfoRepository $paypalAuthorizationInfoRepository
     * @param Session $checkoutSession
     * @param CollectionFactory $collectionFactory
     * @param DateTime $time
     */
    public function __construct(
        PaypalAuthorizationFactory        $paypalAuthorizationFactory,
        ScopeConfigInterface              $scopeConfig,
        PaypalAuthorizationInfoRepository $paypalAuthorizationInfoRepository,
        Session                           $checkoutSession,
        CollectionFactory                 $collectionFactory,
        DateTime $time
    )
    {
        $this->paypalAuthorizationFactory = $paypalAuthorizationFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->paypalAuthorizationInfoRepository = $paypalAuthorizationInfoRepository;
        $this->_checkoutSession = $checkoutSession;
        $this->collectionFactory = $collectionFactory;
        $this->time = $time;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        /** @var $order Order */
        $order = $observer->getOrder();

        $method = $order->getPayment()->getMethod();
        if (!in_array($method,self::PAYPAL_METHOD)){
           return;
        }
        if (!$order->isObjectNew()) {
            return;
        }

        $paypalAuthorization = $this->paypalAuthorizationFactory->create();
        $orderIncrementId = $order->getIncrementId();
        $payment = $order->getPayment();
        $settlementAmount = $payment->getAdditionalInformation('settlement_amount');
        $createTime = $payment->getAdditionalInformation('create_time');
        $authorizationPeriod = $this->getAuthorizationPeriod($createTime);
        $honorPeriod = $this->getHonorPeriod($createTime);
        $paypalAuthorization->setOrderIncrementId($orderIncrementId);
        $paypalAuthorization->setSettlementAmount($settlementAmount);
        if ($payment->getAdditionalInformation('intent') === self::INTENT_AUTHORIZE) {
            $authorizationID = $payment->getAdditionalInformation('authorization_id');
            $paypalAuthorization->setPayPalAuthorizationId($authorizationID);
            $paypalAuthorization->setPayPalAuthorizationPeriod($authorizationPeriod);
            $paypalAuthorization->setPayPalHonorPeriod($honorPeriod);
            $paypalAuthorization->setPayPalStatus(self::PAYPAL_STATUS['Authorized']);
            $paypalAuthorization->setPayPalAuthorizeAt($createTime);
        } else {
            $capturedID = $payment->getAdditionalInformation('captured_id');
            $paypalAuthorization->setPayPalCaptureId($capturedID);
            $paypalAuthorization->setPayPalStatus(self::PAYPAL_STATUS['Captured']);
            $paypalAuthorization->setPayPalCapturedAt($createTime);
        }
        $this->paypalAuthorizationInfoRepository->save($paypalAuthorization);
    }

    /**
     * @param $field
     * @return mixed
     */
    public function getConfigValue($field)
    {
        return $this->_scopeConfig->getValue(
            $this->_preparePathConfig($field),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $field
     * @return string
     */
    protected function _preparePathConfig($field): string
    {
        return sprintf('payment/%s/%s', self::CODE, $field);
    }

    /**
     * @param $date
     * @return mixed
     */
    public function formatDate($date){

        return date(self::FORMAT_DATE,$date);
    }

    /**
     * @param $createTime
     * @return mixed
     */
    public function getAuthorizationPeriod($createTime){
    $configPeriod = $this->getConfigValue(self::PAYPAL_AUTHORIZATION_PERIOD);
    $authorizationPeriod = strtotime( $createTime . ' + ' . $configPeriod . 'days' );
    return $this->formatDate($authorizationPeriod);
    }

    /**
     * @param $createTime
     * @return mixed
     */
    public function getHonorPeriod($createTime){
    $configHonorPeriod = $this->getConfigValue(self::PAYPAL_AUTHORIZATION_HONOR_PERIOD);
    $authorizationPeriod = strtotime( $createTime . ' + ' . $configHonorPeriod . 'days' );
    return $this->formatDate($authorizationPeriod);
    }


}
