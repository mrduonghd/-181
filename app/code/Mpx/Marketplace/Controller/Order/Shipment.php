<?php

namespace Mpx\Marketplace\Controller\Order;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\DB\Transaction;

/**
 * Save shipment qty
 *
 * Class Shipment
 */
class Shipment extends \Webkul\Marketplace\Controller\Order\Shipment
{
    /**
     * Save shipment
     *
     * @return Redirect|Page
     */
    public function execute()
    {
        $helper = $this->helper;
        $orderId = $this->getRequest()->getParam('order_id');
        $isPartner = $helper->isSeller();
        if ($isPartner != 1) {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        if ($order = $this->_initOrder()) {
            $shipment = $this->doShipmentExecution($order);
            if ($shipment && $shipment->getId()) {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/order_shipment/view/order_id/' . $orderId . '/shipment_id/' . $shipment->getId() . '/',
                    [
                        'order_id' => $order->getEntityId(),
                        '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
            }
        }
        return $this->resultRedirectFactory->create()->setPath(
            'mpx/order_shipment/newshipment',
            [
                'order_id' => $order->getEntityId(),
                '_secure' => $this->getRequest()->isSecure(),
            ]
        );
    }

    /**
     * Check order
     *
     * @return false|OrderInterface
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        try {
            $order = $this->_orderRepository->get($id);
            $tracking = $this->orderHelper->getOrderinfo($id);
            if ($tracking) {
                if ($tracking->getOrderId() == $id) {
                    if (!$id) {
                        $this->messageManager->addError(__('This order no longer exists.'));
                        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

                        return false;
                    }
                } else {
                    $this->messageManager->addError(__('You are not authorize to manage this order.'));
                    $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

                    return false;
                }
            } else {
                $this->messageManager->addError(__('You are not authorize to manage this order.'));
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

                return false;
            }
        } catch (NoSuchEntityException $e) {
            $this->helper->logDataInLogger(
                "Controller_Order execute : " . $e->getMessage()
            );
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

            return false;
        } catch (InputException $e) {
            $this->helper->logDataInLogger(
                "Controller_Order execute : " . $e->getMessage()
            );
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);

            return false;
        }
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);

        return $order;
    }

    /**
     * Create Shipment
     *
     * @param OrderInterface $order
     * @return false
     */
    protected function doShipmentExecution($order)
    {

        try {
            $sellerId = $this->_customerSession->getCustomerId();
            $orderId = $order->getId();
            $trackingId = '';
            $carrier = '';
            $shippingLabel = '';
            $trackingData = [];
            $paramData = $this->getRequest()->getParams();
            if (!empty($paramData['number'])) {
                $trackingId = $paramData['number'];
                $trackingData[1]['number'] = $trackingId;
                $trackingData[1]['carrier_code'] = $paramData['carrier'];
            }
            if (!empty($paramData['carrier'])) {
                $trackingData[1]['title'] = $paramData['title'];
            }
            if ($order->canUnhold()) {
                $this->messageManager->addError(
                    __('Can not create shipment as order is in HOLD state')
                );
                return;
            }
            if ($trackingId === '') {
                return;
            }

            $shipmentItems = $paramData['shipment']['items'];
            $itemsArray = $this->_getShippingItemQtys($order, $shipmentItems);
            if (count($itemsArray) === 0) {
                return;
            }
            if ($order->getForcedDoShipmentWithInvoice()) {
                $this->messageManager
                    ->addError(
                        __('Cannot do shipment for the order separately from invoice.')
                    );
                return;
            }
            if (!$order->canShip()) {
                $this->messageManager->addError(
                    __('Cannot do shipment for the order.')
                );
                return;
            }
            $shipment = $this->_prepareShipment(
                $order,
                $itemsArray['data'],
                $trackingData
            );
            if ($shippingLabel != '') {
                $shipment->setShippingLabel($shippingLabel);
            }

            if ($shipment) {
                $shipment->getOrder()->setCustomerNoteNotify(
                    !empty($data['send_email'])
                );
                $isNeedCreateLabel = !empty($shippingLabel) && $shippingLabel;
                $shipment->getOrder()->setIsInProcess(true);
                if (!empty($paramData['shipment']['comment_text'])) {
                    $shipment->addComment(
                        $paramData['shipment']['comment_text'],
                        isset($paramData['shipment']['comment_customer_notify']),
                        isset($paramData['shipment']['is_visible_on_front'])
                    );

                    $shipment->setCustomerNote($paramData['shipment']['comment_text']);
                    $shipment->setCustomerNoteNotify(isset($paramData['shipment']['comment_customer_notify']));
                }
                $transactionSave = $this->_objectManager->create(
                    Transaction::class
                )->addObject(
                    $shipment
                )->addObject(
                    $shipment->getOrder()
                );
                try {
                    $transactionSave->save();
                }catch (\Exception $e){
                    $this->messageManager->addErrorMessage('Error cannot save shipment');
                    return false;
                }


                $sellerCollection = $this->mpOrdersModel->create()
                    ->getCollection()
                    ->addFieldToFilter(
                        'order_id',
                        ['eq' => $orderId]
                    )
                    ->addFieldToFilter(
                        'seller_id',
                        ['eq' => $sellerId]
                    );
                foreach ($sellerCollection as $row) {
                    if ($shipment->getId() != '') {
                        $row->setShipmentId($shipment->getId());
                        $row->setTrackingNumber($trackingId);
                        $row->setCarrierName($carrier);
                        if ($row->getInvoiceId()) {
                            $row->setOrderStatus('complete');
                        } else {
                            $row->setOrderStatus('processing');
                        }
                        $row->save();
                    }
                }
                if (!empty($paramData['shipment']['send_email'])) {
                    $this->_shipmentSender->send($shipment);
                }
                $this->_customerSession->setData('tracking_code', $paramData['carrier']);
                $this->_customerSession->setData('tracking_title', $paramData['title']);
                $shipmentCreatedMessage = __('The shipment has been created.');
                $labelMessage = __('The shipping label has been created.');
                $this->messageManager->addSuccess(
                    $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelMessage
                        : $shipmentCreatedMessage
                );
                return $shipment;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Shipment doShipmentExecution : " . $e->getMessage()
            );
            $this->messageManager->addError(
                __('We can\'t save the shipment right now.')
            );
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Change qty order shipment
     *
     * @param OrderInterface $order
     * @param array $shipmentItems
     * @return array
     */
    protected function _getShippingItemQtys($order, $shipmentItems): array
    {

        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (array_key_exists($item->getItemId(), $shipmentItems)) {
                $data[$item->getItemId()] = $shipmentItems[$item->getItemId()];
                $_item = $item;

                if ($_item->getParentItem()) {
                    continue;
                }

                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }

        return ['data' => $data, 'subtotal' => $subtotal, 'baseSubtotal' => $baseSubtotal];
    }
}
