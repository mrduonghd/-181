<?php

namespace Mpx\PaypalJs\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order;
use Mpx\PaypalJs\Model\PaypalAuthorizationFactory;
use Mpx\PaypalJs\Model\ResourceModel\PaypalAuthorization;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Save all shipment at
 *
 * class ShipmentSaveAfter
 */
class ShipmentSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var DateTime
     */
    protected $timezoneInterface;

    /**
     * @var PaypalAuthorizationFactory
     */
    private $_paypalAuthorizationFactory;

    /**
     * @var PaypalAuthorization
     */
    private $_paypalAuthorizationResource;

    /**
     * @var ManagerInterface
     */
    protected $_message;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param PaypalAuthorizationFactory $paypalAuthorizationFactory
     * @param PaypalAuthorization $paypalAuthorizationResource
     * @param DateTime $timezoneInterface
     * @param LoggerInterface $logger
     * @param ManagerInterface $_message
     */
    public function __construct(
        PaypalAuthorizationFactory $paypalAuthorizationFactory,
        PaypalAuthorization        $paypalAuthorizationResource,
        DateTime                   $timezoneInterface,
        LoggerInterface            $logger,
        ManagerInterface           $_message
    ) {
        $this->_paypalAuthorizationFactory = $paypalAuthorizationFactory;
        $this->_paypalAuthorizationResource = $paypalAuthorizationResource;
        $this->timezoneInterface = $timezoneInterface;
        $this->logger = $logger;
        $this->_message = $_message;
    }

    /**
     * Save all shipment at
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $shipment = $observer->getShipment();
        $order = $shipment->getOrder();
        $paypalFactory = $this->_paypalAuthorizationFactory->create();
        $paypalFactory->getByIncrementId($order->getIncrementId());
        if ($this->isAllItemShipped($order)) {
            $dateTime = $this->timezoneInterface->gmtDate('Y-m-d H:i:s');
            $paypalFactory->setAllShippingAt($dateTime);
        } else {
            $paypalFactory->setAllShippingAt(null);
        }
        $this->_paypalAuthorizationResource->save($paypalFactory);
    }

    /**
     * Check qty shipment
     *
     * @param Order  $order
     * @return bool
     */
    public function isAllItemShipped(Order $order): bool
    {
        $orderItems = $order->getAllVisibleItems();
        foreach ($orderItems as $item) {
            if (round($item->getQtyOrdered()) !== round($item->getQtyShipped())) {
                return false;
            }
        }
        return true;
    }
}
