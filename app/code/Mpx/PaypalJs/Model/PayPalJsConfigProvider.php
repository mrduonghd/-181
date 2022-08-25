<?php

namespace Mpx\PaypalJs\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mpx\PaypalJs\Logger\Handler;
use Webkul\Marketplace\Helper\Data;

/**
 * Class PayPalJsConfigProvider
 * get config PayPal push checkout config
 */
class PayPalJsConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const BASE_URL_SDK = 'https://www.paypal.com/sdk/js?';

    const SDK_CONFIG_CLIENT_ID  = 'client-id';
    const SDK_CONFIG_CURRENCY   = 'currency';
    const SDK_CONFIG_DEBUG      = 'debug';
    const SDK_CONFIG_COMPONENTS = 'components';
    const SDK_CONFIG_LOCALE     = 'locale';
    const SDK_CONFIG_INTENT     = 'intent';
    const LENGTH_IDENTIFIER = 15;

    /**
     * @var string
     */
    protected $_payment_code = Config::PAYMENT_CODE;
    /**
     * @var array
     */
    protected $_params = [];

    /** @var Config */
    protected $_paypalConfig;

    /** @var Session */
    protected $_customerSession;

    /** @var \Magento\Checkout\Model\Session */
    protected $_checkoutSession;

    /** @var Handler */
    protected $_logger;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @param Config $paypalConfig
     * @param Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Handler $logger
     * @param Data $data
     */
    public function __construct(
        Config                          $paypalConfig,
        Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        Handler    $logger,
        Data $data
    ) {
        $this->_paypalConfig    = $paypalConfig;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_logger          = $logger;
        $this->data = $data;
    }

    /**
     * Get data config  PaypalJs
     *
     * @return array|\array[][]
     */
    public function getConfig(): array
    {
        if (!$this->_paypalConfig->isMethodActive($this->_payment_code)) {
            return [];
        }
        $quote = $this->_checkoutSession->getQuote();
        $firstItem = $quote->getItemsCollection()->getFirstItem();
        $sellerId = $this->data->getSellerIdByProductId($firstItem->getProductId());
        $sellerIdZeroFill = str_pad($sellerId, 3, "0", STR_PAD_LEFT);
        $invoiceID = $sellerIdZeroFill . "-" . $quote->getReservedOrderId();

        $config = [
            'payment' => [
                $this->_payment_code => [
                    'title' => $this->_paypalConfig->getConfigValue(Config::CONFIG_XML_TITLE),
                    'urlSdk' => $this->getUrlSdk(),
                    'customer' => [
                        'id' => $this->validateCustomerId(),
                    ],
                    self::SDK_CONFIG_INTENT => $this->_paypalConfig->getIntent(),
                    self::SDK_CONFIG_DEBUG => $this->_paypalConfig->isSetFLag(Config::CONFIG_XML_DEBUG_MODE),
                    'activeCard' => $this->_paypalConfig->getActiveCard(),
                    'invoice_id' => $invoiceID,
                    'credit_card_title' => $this->_paypalConfig->getConfigValue(Config::CONFIG_XML_CREDIT_CARD_TITLE),
                    self::SDK_CONFIG_CURRENCY => $this->_paypalConfig->getCurrency(),
                ]
            ]
        ];
        $this->_logger->debug(__METHOD__ . ' | CONFIG ' . var_export($config, true));

        return $config;
    }

    /**
     * Get Url Sdk
     *
     * @return string
     */
    public function getUrlSdk(): string
    {
        $this->buildParams();

        return self::BASE_URL_SDK . http_build_query($this->_params);
    }

    /**
     * Build params for js sdk
     *
     * @return void
     */
    private function buildParams(): void
    {
        $this->_params = [
            self::SDK_CONFIG_CLIENT_ID  => $this->_paypalConfig->getClientId(),
            self::SDK_CONFIG_CURRENCY   => $this->_paypalConfig->getCurrency(),
            self::SDK_CONFIG_DEBUG      => $this->_paypalConfig
                                                ->isSetFLag(Config::CONFIG_XML_DEBUG_MODE) ? 'true' : 'false',
            self::SDK_CONFIG_COMPONENTS => 'hosted-fields,buttons,funding-eligibility',
            self::SDK_CONFIG_LOCALE     => 'ja_JP',
            self::SDK_CONFIG_INTENT     => $this->_paypalConfig->getIntent(),
        ];
    }

    /**
     * Validate CustomerId
     *
     * @return int|void|null
     */
    private function validateCustomerId()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return $this->_customerSession->getCustomerId();
        }
    }
}
