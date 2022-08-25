<?php
declare(strict_types=1);

namespace Mpx\PaypalJs\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mpx\PaypalJs\Api\Data\PaypalAuthorizationInfoInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class PaypalAuthorization extends AbstractModel implements PaypalAuthorizationInfoInterface, IdentityInterface
{

    /**
    * Cache tag paypal
    */
    const CACHE_TAG = 'paypal_checkout_info';

    /**
     * @var DateTime
     */
    protected $time;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'paypal_checkout_info';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $time
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $time,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->time = $time;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init resource model and id field
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Mpx\PaypalJs\Model\ResourceModel\PaypalAuthorization::class);
        $this->setIdFieldName('id');
    }

    /**
     * Get cache identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }


    /**
     * @param $incrementId
     * @return PaypalAuthorization
     */
    public function getByIncrementId($incrementId): PaypalAuthorization
    {
        return $this->load($incrementId, PaypalAuthorizationInfoInterface::ORDER_INCREMENT_ID);
    }

    /**
     * Get order increment ID
     *
     * @return int
     */
    public function getOrderIncrementId(): int
    {
        return $this->getData(PaypalAuthorizationInfoInterface::ORDER_INCREMENT_ID);
    }

    /**
     * Get authorization id
     *
     * @return string
     */
    public function getPayPalAuthorizationId(): string
    {
        return $this->getData(PaypalAuthorizationInfoInterface::PAYPAL_AUTHORIZATION_ID);
    }

    /**
     * Get Authorization Period
     *
     * @return string
     */
    public function getPayPalAuthorizationPeriod(): string
    {
        return $this->getData(PaypalAuthorizationInfoInterface::PAYPAL_AUTHORIZATION_PERIOD);
    }

    /**
     * Get Honor Period
     *
     * @return string
     */
    public function getPayPalHonorPeriod(): string
    {
        return $this->getData(PaypalAuthorizationInfoInterface::PAYPAL_HONOR_PERIOD);
    }

    /**
     * Get paypal status
     *
     * @return string
     */
    public function getPayPalStatus(): string
    {
        return $this->getData(PaypalAuthorizationInfoInterface::PAYPAL_STATUS);
    }

    /**
     * Get create at
     *
     * @return string
     */
    public function getCreateAt(): string
    {
        return $this->getData(PaypalAuthorizationInfoInterface::CREATE_AT);
    }

    /**
     * Get update at
     *
     * @return string
     */
    public function getUpdateAt(): string
    {
        return $this->getData(PaypalAuthorizationInfoInterface::UPDATE_AT);
    }

    /**
     * Get authorize at
     *
     * @return string
     */
    public function getPayPalAuthorizeAt(): string
    {
        return $this->getData(PaypalAuthorizationInfoInterface::PAYPAL_AUTHORIZED_AT);
    }

    /**
     * Set Order Increment ID
     *
     * @param $orderIncrementId
     * @return PaypalAuthorizationInfoInterface
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        return $this->setData(PaypalAuthorizationInfoInterface::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * Set Settlement Amount
     *
     * @param $settlementAmount
     * @return PaypalAuthorizationInfoInterface
     */
    public function setSettlementAmount($settlementAmount) : PaypalAuthorizationInfoInterface
    {
        return $this->setData(PaypalAuthorizationInfoInterface::ORDER_SETTLEMENT_AMOUNT, $settlementAmount);
    }

    /**
     * Set capture id
     *
     * @param $captureId
     * @return PaypalAuthorizationInfoInterface
     */
    public function setPayPalCaptureId($captureId): PaypalAuthorizationInfoInterface
    {
        return $this->setData(PaypalAuthorizationInfoInterface::PAYPAL_CAPTURE_ID, $captureId);
    }

    /**
     * Set authorization id
     *
     * @param $authorizationId
     * @return PaypalAuthorizationInfoInterface
     */
    public function setPayPalAuthorizationId($authorizationId): PaypalAuthorizationInfoInterface
    {
        return $this->setData(PaypalAuthorizationInfoInterface::PAYPAL_AUTHORIZATION_ID, $authorizationId);
    }

    /**
     * Set authorization id
     *
     * @param $authorizationPeriod
     * @return PaypalAuthorizationInfoInterface
     */
    public function setPayPalAuthorizationPeriod($authorizationPeriod): PaypalAuthorizationInfoInterface
    {
        return $this->setData(PaypalAuthorizationInfoInterface::PAYPAL_AUTHORIZATION_PERIOD, $authorizationPeriod);
    }

    /**
     * Set honor period
     *
     * @param $honorPeriod
     * @return PaypalAuthorizationInfoInterface
     */
    public function setPayPalHonorPeriod($honorPeriod): PaypalAuthorizationInfoInterface
    {
        return $this->setData(PaypalAuthorizationInfoInterface::PAYPAL_HONOR_PERIOD, $honorPeriod);
    }

    /**
     * Set paypal status
     *
     * @param $status
     * @return PaypalAuthorizationInfoInterface
     */
    public function setPayPalStatus($status): PaypalAuthorizationInfoInterface
    {
        return $this->setData(PaypalAuthorizationInfoInterface::PAYPAL_STATUS, $status);
    }

    /**
     * Set create at
     *
     * @param $createAt
     * @return PaypalAuthorizationInfoInterface
     */
    public function setCreateAt($createAt): PaypalAuthorizationInfoInterface
    {
        return $this->setData(PaypalAuthorizationInfoInterface::CREATE_AT, $createAt);
    }

    /**
     * Set update at
     *
     * @param $updateAt
     * @return PaypalAuthorizationInfoInterface
     */
    public function setUpdateAt($updateAt): PaypalAuthorizationInfoInterface
    {
        return $this->setData(PaypalAuthorizationInfoInterface::UPDATED_AT, $updateAt);
    }

    /**
     * Set authorize at
     *
     * @param $authorizeAt
     * @return PaypalAuthorizationInfoInterface
     */
    public function setPayPalAuthorizeAt($authorizeAt): PaypalAuthorizationInfoInterface
    {
        return $this->setData(PaypalAuthorizationInfoInterface::PAYPAL_AUTHORIZED_AT, $authorizeAt);
    }
    /**
     * Set captured at
     *
     * @param $capturedAt
     * @return PaypalAuthorizationInfoInterface
     */
    public function setPayPalCapturedAt($capturedAt): PaypalAuthorizationInfoInterface
    {
        return $this->setData(PaypalAuthorizationInfoInterface::PAYPAL_CAPTURED_AT, $capturedAt);
    }

    public function getAllShippingAt()
    {
        return $this->getData(PaypalAuthorizationInfoInterface::ALL_SHIPPING_AT);
    }

    public function setAllShippingAt($allShippingAt)
    {
        return $this->setData(PaypalAuthorizationInfoInterface::ALL_SHIPPING_AT, $allShippingAt);
    }
}
