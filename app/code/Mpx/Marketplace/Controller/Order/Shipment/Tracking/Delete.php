<?php
/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */

namespace Mpx\Marketplace\Controller\Order\Shipment\Tracking;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends \Webkul\Marketplace\Controller\Order\Shipment\Tracking\Delete
{
    /**
     * Add new tracking number action
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $trackId = $this->getRequest()->getParam('id');
            if ($shipment = $this->_initShipment()) {
                $track = $this->_objectManager->create(
                    \Magento\Sales\Model\Order\Shipment\Track::class
                )->load($trackId);
                if ($track->getId()) {
                    $track->delete();
                    $response = [
                        'error' => false
                    ];
                } else {
                    $response = [
                        'error' => true,
                        'message' => __(
                            'We can\'t load track with retrieving identifier right now.%1'
                        )
                    ];
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => __(
                        'We can\'t initialize shipment for adding tracking number.'
                    ),
                ];
            }
        } catch (\Exception $e) {
            $response = [
                'error' => true,
                'message' => __('We can\'t delete tracking number.')
            ];
            $this->helper->logDataInLogger(
                "Controller_Order_Shipment_Tracking_Delete execute : ".$e->getMessage()
            );
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get(
                \Magento\Framework\Json\Helper\Data::class
            )->jsonEncode($response);
            $this->getResponse()->representJson($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }

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
                if (!$shipmentId) {
                    $this->messageManager->addError(__('The shipment no longer exists.'));
                    $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

                    return false;
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
