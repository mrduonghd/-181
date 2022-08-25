<?php

namespace Mpx\Marketplace\Controller\Order\Shipment\Tracking;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class Add extends \Webkul\Marketplace\Controller\Order\Shipment\Tracking\Add
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
            $carrier = $this->getRequest()->getPost('carrier');
            $number = $this->getRequest()->getPost('number');
            $title = $this->getRequest()->getPost('title');
            $orderId = $this->getRequest()->getParam('order_id');
            $shipmentId = $this->getRequest()->getParam('shipment_id');
            if (empty($carrier)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please specify a carrier.')
                );
            }
            if (empty($number)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please enter a tracking number.')
                );
            }
            if ($shipment = $this->_initShipment()) {
                $track = $this->_objectManager->create(
                    \Magento\Sales\Model\Order\Shipment\Track::class
                )->setNumber(
                    $number
                )->setCarrierCode(
                    $carrier
                )->setTitle(
                    $title
                );
                $shipment->addTrack($track)->save();
                $trackId  = $track->getId();
                if ($track->isCustom()) {
                    $numberclass = 'display';
                    $numberclasshref = 'no-display';
                    $trackingPopupUrl = '';
                } else {
                    $numberclass = 'no-display';
                    $numberclasshref = 'display';
                    $trackingPopupUrl = $this->_objectManager->create(
                        \Magento\Shipping\Helper\Data::class
                    )->getTrackingPopupUrlBySalesModel($track);
                }
                $this->_customerSession->setData('tracking_code', $carrier);
                $this->_customerSession->setData('tracking_title', $title);
                $response = [
                    'error' => false,
                    'carrier' => $this->_objectManager->create(
                        \Webkul\Marketplace\Block\Order\View::class
                    )->getCarrierTitle($carrier),
                    'title' => $title,
                    'number' => $number,
                    'numberclass' => $numberclass,
                    'numberclasshref' => $numberclasshref,
                    'trackingPopupUrl' => $trackingPopupUrl,
                    'trackingDeleteUrl' =>  $this->_objectManager->create(
                        \Magento\Framework\UrlInterface::class
                    )->getUrl(
                        'marketplace/order_shipment_tracking/delete',
                        [
                            'order_id' => $orderId,
                            'shipment_id' => $shipmentId,
                            'id' => $trackId,
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    )
                ];
            } else {
                $response = [
                    'error' => true,
                    'message' => __(
                        'We can\'t initialize shipment for adding tracking number.'
                    ),
                ];
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Shipment_Tracking_Add execute : ".$e->getMessage()
            );
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Shipment_Tracking_Add execute : ".$e->getMessage()
            );
            $response = [
                'error' => true,
                'message' => __('Cannot add tracking number.%1', $e->getMessage())
            ];
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
