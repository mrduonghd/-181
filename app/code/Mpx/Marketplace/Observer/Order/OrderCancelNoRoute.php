<?php

namespace Mpx\Marketplace\Observer\Order;

use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Sales\Model\Order;

class OrderCancelNoRoute implements ObserverInterface
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var ForwardFactory
     */
    protected $forwardFactory;

    /**
     * @param ForwardFactory $forwardFactory
     * @param Order $order
     */
    public function __construct(
        ForwardFactory $forwardFactory,
        Order $order
    ) {
        $this->forwardFactory = $forwardFactory;
        $this->order = $order;
    }

    /**
     * Forward page 404
     *
     * @param Observer $observer
     * @return Forward|void
     */
    public function execute(Observer $observer)
    {
        if (isset($observer->getRequest()->getParams()["id"])){
            $orderId = $observer->getRequest()->getParams()["id"];
            $order = $this->order->load($orderId);
            if ($order->getShipmentsCollection()->count()>0) {
                $resultForward = $this->forwardFactory->create();
                $resultForward->setController('index');
                $resultForward->forward('defaultNoRoute');
                return $resultForward;
            }
        }
    }
}
