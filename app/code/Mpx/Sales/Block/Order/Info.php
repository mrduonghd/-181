<?php

namespace Mpx\Sales\Block\Order;

/**
 * Display order page info
 * Class View
 */
class Info extends \Magento\Sales\Block\Order\Info
{
    /**
     * Return page order info
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Order Number %1', $this->getOrder()->getRealOrderId()));
        $infoBlock = $this->paymentHelper->getInfoBlock($this->getOrder()->getPayment(), $this->getLayout());
        $this->setChild('payment_info', $infoBlock);
    }
}
