<?php

namespace Mpx\Marketplace\Controller\Order\Shipment;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * Display page new shipment
 *
 * class  NewShipment
 */
class NewShipment extends \Webkul\Marketplace\Controller\Order
{

    /**
     * Display page new shipment
     *
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $helper = $this->helper;
        $isPartner = $helper->isSeller();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->_orderRepository->get($orderId);
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);
        if ($isPartner == 1) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->_resultPageFactory->create();
            if ($helper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpx_order_shipment_newshipment');
            }
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
