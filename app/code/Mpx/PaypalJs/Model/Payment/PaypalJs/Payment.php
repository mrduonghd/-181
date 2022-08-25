<?php

namespace Mpx\PaypalJs\Model\Payment\PaypalJs;

use Magento\Checkout\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Exception;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Mpx\PaypalJs\Logger\Handler;
use Mpx\PaypalJs\Model\Config;
use Mpx\PaypalJs\Block\Info;

/**
 * Class Payment
 * create transaction order
 */
class Payment extends AbstractMethod
{
    const CODE                       = 'paypaljs';
    const GATEWAY_NOT_TXN_ID_PRESENT = 'The transaction id is not present';
    const INTENT_CAPTURE             = 'CAPTURE';
    const INTENT_AUTHORIZE           = 'AUTHORIZE';


    /**
     * @var string
     */
    protected $_infoBlockType = Info::class;
    /**
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * @var bool
     */
    protected $_isGateway = true;
    /**
     * @var bool
     */
    protected $_canCapture = true;
    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /** @var Order */
    protected $_order = false;

    /**
     * @var
     */
    protected $_response;

    /** @var Handler */
    protected $_logger;

    /** @var ScopeConfigInterface */
    protected $_scopeConfig;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $paypalConfig;


    private $_checkoutSession;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param Logger $paymentLogger
     * @param ScopeConfigInterface $scopeConfig
     * @param Handler $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param ManagerInterface $eventManager
     * @param Config $paypalConfig
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        Context                    $context,
        Registry                   $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory      $customAttributeFactory,
        Data                       $paymentData,
        Logger                     $paymentLogger,
        ScopeConfigInterface       $scopeConfig,
        Handler                    $logger,
        AbstractResource           $resource = null,
        AbstractDb                 $resourceCollection = null,
        ManagerInterface           $eventManager,
        Config                     $paypalConfig,
        TransportBuilder           $transportBuilder,
        StoreManagerInterface      $storeManager,
        Session                    $checkoutSession,
        array                      $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $paymentLogger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_eventManager = $eventManager;
        $this->checkoutSession = $checkoutSession;
        $this->paypalConfig = $paypalConfig;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Payment capturing
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws Exception|\Exception
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount): Payment
    {
        $this->processTransaction($payment);
        return $this;
    }

    /**
     * @param DataObject $data
     * @return $this|Payment
     * @throws LocalizedException
     */
    public function assignData(DataObject $data): Payment
    {
        parent::assignData($data);

        $infoInstance = $this->getInfoInstance();
        $infoInstance->setAdditionalInformation('payment_source');

        $additionalData = $data->getData('additional_data') ?: $data->getData();

        foreach ($additionalData as $key => $value) {
            if (!is_object($value)) {
                $infoInstance->setAdditionalInformation($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this|Payment
     * @throws \Exception
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount): Payment
    {
        $this->processTransaction($payment);
        return $this;
    }

    /**
     * @param $payment
     * @return mixed
     * @throws \Exception
     */
    protected function processTransaction(&$payment)
    {
        $_txnId = $payment->getAdditionalInformation('order_id');
        $intent = $payment->getAdditionalInformation('intent');

        if (!$_txnId) {
            $errorMessage = self::GATEWAY_NOT_TXN_ID_PRESENT;
            throw new \RuntimeException(__($errorMessage));
        }

        $payment->setTransactionId($_txnId)
            ->setIsTransactionClosed(false);

        switch ($intent) {
            case self::INTENT_AUTHORIZE:
                $payment->setTransactionId($_txnId)
                    ->setIsTransactionPending(false)
                    ->setIsTransactionClosed(false);
                break;
            case self::INTENT_CAPTURE:
                $payment->setTransactionId($_txnId)
                    ->setIsTransactionClosed(true);
                break;
            default:
                $payment->setIsTransactionPending(false);
                break;
        }
        return $payment;
    }

    /**
     * @param $order
     * @param $comment
     * @param $isCustomerNotified
     * @return mixed
     */
    public function setComments(&$order, $comment, $isCustomerNotified)
    {
        $history = $order->addStatusHistoryComment($comment, false);
        $history->setIsCustomerNotified($isCustomerNotified);

        return $order;
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
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if ('order_place_redirect_url' === $field) {
            return $this->getOrderPlaceRedirectUrl();
        }
        if (null === $storeId) {
            $storeId = $this->getStore();
        }

        if ('sort_order' === $field) {
            $path = 'payment/paypaljs/' . $field;
        } else {
            $path = 'payment/' . $this->_code . '/' . $field;
        }
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
