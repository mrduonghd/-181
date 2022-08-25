<?php
/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */

namespace Mpx\Marketplace\Controller\Order\Shipment;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class Printpdf extends \Webkul\Marketplace\Controller\Order\Shipment\Printpdf
{

    /**
     * Initialize shipment model instance.
     *
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _initShipment()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$shipmentId) {
            return false;
        }
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->_shipment->load($shipmentId);
        if (!$shipment) {
            return false;
        }
        try {
            $order = $this->_orderRepository->get($orderId);
            $tracking = $this->orderHelper->getOrderinfo($orderId);
            if ($tracking) {
                if ($tracking->getShipmentId() == $shipmentId) {
                    if (!$shipmentId) {
                        $this->messageManager->addError(__('The shipment no longer exists.'));
                        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

                        return false;
                    }
                }
            } else {
                $this->messageManager->addError(__('You are not authorize to view this shipment.'));
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

                return false;
            }
        } catch (NoSuchEntityException $e) {
            $this->helper->logDataInLogger(
                "Controller_Order execute : ".$e->getMessage()
            );
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

            return false;
        } catch (InputException $e) {
            $this->helper->logDataInLogger(
                "Controller_Order execute : ".$e->getMessage()
            );
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

            return false;
        }
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);
        $this->_coreRegistry->register('current_shipment', $shipment);

        return $shipment;
    }
}
