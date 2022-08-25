<?php

namespace Mpx\PaypalJs\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SetReservedOrderId implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();
        $quote->reserveOrderId();
    }
}
