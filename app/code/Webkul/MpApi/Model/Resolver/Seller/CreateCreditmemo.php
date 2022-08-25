<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver\Seller;

use Magento\Authorization\Model\UserContextInterface;
use Webkul\MpApi\Model\Seller\SellerManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\Order\CreditmemoFactory;

/**
 * Book field resolver, used for GraphQL request processing
 */
class CreateCreditmemo implements ResolverInterface
{
    protected $sellerManagement;

    /**
     *
     * @param SellerManagement $sellerManagement
     */
    public function __construct(
        SellerManagement $sellerManagement,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagementInterface,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrdersFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Webkul\MpApi\Api\Data\ResponseInterface $responseInterface,
        \Webkul\Marketplace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->sellerManagement = $sellerManagement;
        $this->creditmemoManagementInterface = $creditmemoManagementInterface;
        $this->mpOrdersFactory = $mpOrdersFactory;
        $this->orderFactory = $orderFactory;
        $this->saleslistFactory = $saleslistFactory;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->responseInterface = $responseInterface;
        $this->productMetadata = $productMetadata;
        $this->sellerFactory = $sellerFactory;
    }
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ((!$context->getUserId()) || $context->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            throw new GraphQlAuthorizationException(
                __(
                    'Current customer does not have access to the resource "%1"',
                    [\Magento\Customer\Model\Customer::ENTITY]
                )
            );
        }
        if (!isset($args['orderid'])) {
            throw new GraphQlInputException(
                __("'orderid' input argument is required.")
            );
        }
        foreach ($args['creditmemo']['items'] as $itm) {
            $args['creditmemo']['orderitem'][$itm['itemid']] = ["qty"=>$itm['qty']];
        }
        unset($args['creditmemo']['items']);
        $args['creditmemo']['items'] = $args['creditmemo']['orderitem'];
        $result = $this->createCreditmemo($context->getUserId(), $args['invoiceid'], $args['orderid'], $args);
        if ($result['item']['status'] == 2) {
            throw new GraphQlAuthorizationException(
                __(
                    $result['item']['message']
                )
            );
        }
        return $result['item'];
    }

    public function createCreditmemo($id, $invoiceId, $orderId, $args)
    {
        $sellerId = $id;
        $returnArray = [];
        if ($order = $this->_initOrder($orderId, $sellerId)) {
            try {
                if (!$this->isSeller($id)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('invalid seller')
                    );
                }
                $creditmemo = $this->_initOrderCreditmemo($sellerId, $invoiceId, $order, $args);
                if ($creditmemo) {
                    if (!$creditmemo->isValidGrandTotal()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The credit memo\'s total must be positive.')
                        );
                    }
                    $data = $args;
                    $data = $data['creditmemo'];

                    if (!empty($data['comment_text'])) {
                        $creditmemo->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                        );
                        $creditmemo->setCustomerNote($data['comment_text']);
                        $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                    }

                    if (isset($data['do_offline'])) {
                        //do not allow online refund for Refund to Store Credit
                        if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('Cannot create online refund for Refund to Store Credit.')
                            );
                        }
                    }
                    $creditmemoManagement = $this->creditmemoManagementInterface;
                    $creditmemo = $creditmemoManagement
                        ->refund($creditmemo, (bool) $data['do_offline'], !empty($data['send_email']));

                        /*update records*/
                        $creditmemoIds = [];
                    $trackingcol1 = $this->mpOrdersFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            ['eq' => $orderId]
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $sellerId]
                        );
                    foreach ($trackingcol1 as $tracking) {
                        if ($tracking->getCreditmemoId()) {
                            $creditmemoIds = explode(',', $tracking->getCreditmemoId());
                        }
                        array_push($creditmemoIds, $creditmemo->getId());
                        $tracking->setCreditmemoId(implode(',', $creditmemoIds));
                        $tracking->save();
                    }

                    if (!empty($data['send_email'])) {
                        $this->_creditmemoSender->send($creditmemo);
                    }

                    if (!empty($data['send_email'])) {
                        $this->_creditmemoSender->send($creditmemo);
                    }
                    $returnArray['id'] = $creditmemo->getId();
                    $returnArray['status'] = 1;
                    $returnArray['message'] = __('You created the credit memo.');
                    return $this->getJsonResponse($returnArray);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $returnArray['status'] = 2;
                $returnArray['message'] = __($e->getMessage());
                return $this->getJsonResponse($returnArray);
            } catch (\Exception $e) {
                $this->createLog($e);
                $returnArray['status'] = 0;
                $returnArray['message'] = __($e->getMessage());
                return $this->getJsonResponse($returnArray);
            }
        } else {
            $returnArray['status'] = 0;
            $returnArray['message'] = __('invalid request');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * Initialize order model instance.
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    protected function _initOrder($id, $sellerId)
    {
        try {
            $order = $this->getOrder($id);
            $tracking = $this->getOrderinfo($id, $sellerId);
            if (count($tracking)) {
                if ($tracking->getOrderId() == $id) {
                    if (!$id) {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\NoSuchEntityException $e) {
            return false;
        } catch (\InputException $e) {
            return false;
        }

        return $order;
    }
    
    /**
     * Return the Customer seller status.
     *
     * @return bool|0|1
     */
    public function isSeller($id)
    {
        $sellerId = '';
        $sellerStatus = 0;
        $model = $this->sellerFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $id
            );
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }

        return $sellerStatus;
    }

    /**
     * get order
     *
     * @return Magento\Sales\Model\Order
     */
    private function getOrder($orderId)
    {
        $orderCollection = $this->orderFactory->create()->getCollection()->addFieldToFilter("entity_id", ['eq' => $orderId]);
        if ($orderCollection->getSize() == 0) {
            throw \NoSuchEntityException::singleField('orderId', $orderId);
        } else {
            foreach ($orderCollection as $order) {
                return $order;
            }
        }
        return $this->orderFactory->create();
    }

    /**
     * Return the seller Order data.
     *
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */
    public function getOrderinfo($orderId = '', $sellerId)
    {
        $data = [];
        $model = $this->mpOrdersFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            )
            ->addFieldToFilter(
                'order_id',
                $orderId
            );
        foreach ($model as $tracking) {
            $data = $tracking;
        }

        return $data;
    }

    /**
     * Initialize creditmemo model instance.
     *
     * @return \Magento\Sales\Model\Order\Creditmemo|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _initOrderCreditmemo($sellerId, $invoiceId, $order, $args)
    {
        $refundData = $args;
        $creditmemo = false;

        $sellerId = $sellerId;
        $orderId = $order->getId();

        $invoice = $this->_initCreditmemoInvoice($invoiceId, $order);
        $items = [];
        $itemsarray = [];
        $shippingAmount = 0;
        $codcharges = 0;
        $paymentCode = '';
        $paymentMethod = '';
        if ($order->getPayment()) {
            $paymentCode = $order->getPayment()->getMethod();
        }
        $trackingsdata = $this->mpOrdersFactory->create()->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        );
        foreach ($trackingsdata as $tracking) {
            $shippingAmount = $tracking->getShippingCharges();
            if ($paymentCode == 'mpcashondelivery') {
                $codcharges = $tracking->getCodCharges();
            }
        }
        $codCharges = 0;
        $tax = 0;
        $collection = $this->saleslistFactory->create()->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        );
        foreach ($collection as $saleproduct) {
            if ($paymentCode == 'mpcashondelivery') {
                $codCharges = $codCharges + $saleproduct->getCodCharges();
            }
            $tax = $tax + $saleproduct->getTotalTax();
            array_push($items, $saleproduct['order_item_id']);
        }

        $savedData = $this->_getItemData($order, $items, $args);
        $qtys = [];
        foreach ($savedData as $orderItemId => $itemData) {
            if (isset($itemData['qty'])) {
                $qtys[$orderItemId] = $itemData['qty'];
            }
            if (isset($refundData['creditmemo']['items'][$orderItemId]['back_to_stock'])) {
                $backToStock[$orderItemId] = true;
            }
        }

        if (empty($refundData['creditmemo']['shipping_amount'])) {
            $refundData['creditmemo']['shipping_amount'] = 0;
        }
        if (empty($refundData['creditmemo']['adjustment_positive'])) {
            $refundData['creditmemo']['adjustment_positive'] = 0;
        }
        if (empty($refundData['creditmemo']['adjustment_negative'])) {
            $refundData['creditmemo']['adjustment_negative'] = 0;
        }
        if (!$shippingAmount >= $refundData['creditmemo']['shipping_amount']) {
            $refundData['creditmemo']['shipping_amount'] = 0;
        }
        $refundData['creditmemo']['qtys'] = $qtys;
        if ($invoice) {
            $creditmemo = $this->creditmemoFactory->createByInvoice(
                $invoice,
                $refundData['creditmemo']
            );
        } else {
            $creditmemo = $this->creditmemoFactory->createByOrder(
                $order,
                $refundData['creditmemo']
            );
        }

        /*
         * Process back to stock flags
         */
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            $orderItem = $creditmemoItem->getOrderItem();
            $parentId = $orderItem->getParentItemId();
            if (isset($backToStock[$orderItem->getId()])) {
                $creditmemoItem->setBackToStock(true);
            } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                $creditmemoItem->setBackToStock(true);
            } elseif (empty($savedData)) {
                $creditmemoItem->setBackToStock(
                    $this->_stockConfiguration->isAutoReturnEnabled()
                );
            } else {
                $creditmemoItem->setBackToStock(false);
            }
        }

        return $creditmemo;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return $this|bool
     */
    protected function _initCreditmemoInvoice($invoiceId, $order)
    {
        if ($invoiceId) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            $invoice->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }

        return false;
    }

    /**
     * Get requested items qtys.
     */
    protected function _getItemData($order, $items, $args)
    {
        $refundData = $args;
        $data['items'] = [];
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)
                && isset($refundData['creditmemo']['items'][$item->getItemId()]['qty'])) {
                $data['items'][$item->getItemId()]['qty'] = intval(
                    $refundData['creditmemo']['items'][$item->getItemId()]['qty']
                );

                $_item = $item;
                // for bundle product
                $bundleitems = array_merge([$_item], $_item->getChildrenItems());
                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data['items'][$_bundleitem->getItemId()]['qty'] = intval(
                                $refundData['creditmemo']['items'][$_bundleitem->getItemId()]['qty']
                            );
                        }
                    }
                }
            } else {
                if (!$item->getParentItemId()) {
                    $data['items'][$item->getItemId()]['qty'] = 0;
                }
            }
        }
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = [];
        }

        return $qtys;
    }

    /**
     * getJsonResponse returns json response.
     *
     * @param array $responseContent
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    protected function getJsonResponse($responseContent = [])
    {
        $res = $this->responseInterface;
        $res->setItem($responseContent);
        if (preg_match("/^2\.[0-1]\.\d/", $this->productMetadata->getVersion())) {
            return $res;
        }
        if (preg_match("/^2\.2\.\d/", $this->productMetadata->getVersion())) {
            return $res->getData();
        }
        if (preg_match("/^2\.3\.\d/", $this->productMetadata->getVersion())) {
            return $res->getData();
        }
    }
}
