<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpApi
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpApi\Model\Seller;

use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;

define('DS', DIRECTORY_SEPARATOR);
class SellerManagement implements \Webkul\MpApi\Api\SellerManagementInterface
{
    const SEVERE_ERROR = 0;
    const SUCCESS = 1;
    const LOCAL_ERROR = 2;
    /**
     * $_customerFactory.
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * $_quoteFactory.
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * $_productFactory.
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * $_priceHelper.
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * $_cart.
     *
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $_cartFactory;

    /**
     * $_downloadableConfiguration.
     *
     * @var \Magento\Downloadable\Helper\Catalog\Product\Configuration
     */
    protected $_downloadableConfiguration;

    /**
     * $_checkoutSession.
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * $_orderFactory.
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * $_customerSession.
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * $_coreRegistry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * $_customerRepository.
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * $_country.
     *
     * @var \Magento\Directory\Model\Country
     */
    protected $_country;

    /**
     * $_regionCollection.
     *
     * @var \Magento\Directory\Model\Region
     */
    protected $_regionCollection;

    /**
     * $_storeManager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $_objectCopyService;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * [$_sellerFactory description].
     *
     * @var [type]
     */
    protected $_sellerFactory;

    /**
     * [$_marketplaceHelper description].
     *
     * @var [type]
     */
    protected $_marketplaceHelper;

    /**
     * [$_resultFactory description].
     *
     * @var [type]
     */
    protected $_resultFactory;

    /**
     * [$_objectManager description].
     *
     * @var [type]
     */
    protected $_objectManager;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory
     */
    protected $_sellerlistCollectionFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var OrderManagementInterface
     */
    protected $_orderManagement;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $_invoiceRepository;

    /**
     * @var CreditmemoFactory;
     */
    protected $_creditmemoFactory;

    /**
     * @var InvoiceSender
     */
    protected $_invoiceSender;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * $_resourceConnection.
     *
     * @var \Magento\Framework\App\ResourceConnectionn
     */
    protected $_resourceConnection;

    /**
     * @var ShipmentSender
     */
    protected $_shipmentSender;

    /**
     * @var ShipmentFactory
     */
    protected $_shipmentFactory;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $_eventManager;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $customerInterface;

    protected $encryptorInterface;

    protected $productInterface;

    protected $productRepoInterface;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadata;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Downloadable\Helper\Catalog\Product\Configuration $downloadableConfiguration,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\Data\CustomerInterface $customerInterface,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterface,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Directory\Model\CountryFactory $country,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\Marketplace\Model\SellerFactory $sellerFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        ResultFactory $resultFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerlistCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoFactory $creditmemoFactory,
        InvoiceSender $invoiceSender,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        ShipmentFactory $shipmentFactory,
        ShipmentSender $shipmentSender,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepoInterface,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrdersFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Webkul\Marketplace\Helper\Email $emailHelper,
        \Magento\Backend\Model\Url $backendUrl,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagementInterface,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepositoryInterface,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Webkul\Marketplace\Model\FeedbackFactory $feedbackFactory,
        \Webkul\Marketplace\Model\FeedbackcountFactory $feedbackcountFactory,
        \Webkul\MpApi\Api\Data\ResponseInterface $responseInterface,
        \Magento\Sales\Model\Order\Creditmemo $creditMemoModal
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_quoteFactory = $quoteFactory;
        $this->_productFactory = $productFactory;
        $this->_priceHelper = $priceHelper;
        $this->_cartFactory = $cartFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_customerSession = $customerSession;
        $this->_downloadableConfiguration = $downloadableConfiguration;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerRepository = $customerRepository;
        $this->_country = $country;
        $this->_regionCollection = $regionCollection;
        $this->_storeManager = $storeManager;
        $this->_objectCopyService = $objectCopyService;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_logger = $logger;
        $this->_sellerFactory = $sellerFactory;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_resultFactory = $resultFactory;
        $this->_objectManager = $objectManager;
        $this->_sellerlistCollectionFactory = $sellerlistCollectionFactory;
        $this->_request = $request;
        $this->_orderRepository = $orderRepository;
        $this->_orderManagement = $orderManagement;
        $this->_invoiceRepository = $invoiceRepository;
        $this->_creditmemoFactory = $creditmemoFactory;
        $this->_invoiceSender = $invoiceSender;
        $this->_date = $date;
        $this->_resourceConnection = $resourceConnection;
        $this->_shipmentFactory = $shipmentFactory;
        $this->_shipmentSender = $shipmentSender;
        $this->_eventManager = $eventManager;
        $this->customerInterface = $customerInterface;
        $this->_productMetadata = $productMetadata;
        $this->productInterface = $productInterface;
        $this->encryptorInterface = $encryptorInterface;
        $this->productRepoInterface = $productRepoInterface;
        $this->mpProductFactory = $mpProductFactory;
        $this->saleslistFactory = $saleslistFactory;
        $this->directoryList = $directoryList;
        $this->mpOrdersFactory = $mpOrdersFactory;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
        $this->emailHelper = $emailHelper;
        $this->backendUrl = $backendUrl;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->invoiceFactory = $invoiceFactory;
        $this->urlInterface = $urlInterface;
        $this->orderHelper = $orderHelper;
        $this->creditmemoManagementInterface = $creditmemoManagementInterface;
        $this->creditmemoRepositoryInterface = $creditmemoRepositoryInterface;
        $this->imageFactory = $imageFactory;
        $this->imageHelper = $imageHelper;
        $this->feedbackFactory = $feedbackFactory;
        $this->feedbackcountFactory = $feedbackcountFactory;
        $this->responseInterface = $responseInterface;
        $this->creditMemoModal = $creditMemoModal;
        $header = $this->_request->getHeader('content-type');
        $postValues = $this->_request->getPostValue();
        if ($header == 'application/json') {
            $postValues = @file_get_contents('php://input');
            if ($postValues) {
                $postValues = json_decode($postValues, true);
            }
        }
        $this->_request->setPostValue($postValues);
    }

    /**
     * get order
     *
     * @return Magento\Sales\Model\Order
     */
    private function getOrder($orderId)
    {
        $orderCollection = $this->_orderFactory->create()->getCollection()->addFieldToFilter("entity_id", ['eq' => $orderId]);
        if ($orderCollection->getSize() == 0) {
            throw \NoSuchEntityException::singleField('orderId', $orderId);
        } else {
            foreach ($orderCollection as $order) {
                return $order;
            }
        }
        return $this->_orderFactory->create();
    }

    /**
     * depricated
     *
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerList()
    {
        try {
            $collection = $this->_sellerlistCollectionFactory
                ->create()
                ->addFieldToSelect(
                    '*'
                )
                ->addFieldToFilter(
                    'is_seller',
                    ['eq' => 1]
                )
                ->setOrder(
                    'entity_id',
                    'desc'
                );
            if ($collection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('no sellers found')
                );
            }
            return $this->getJsonResponse(
                $collection->toArray()
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 0;
            return $this->getJsonResponse(
                $returnArray
            );
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 2;
            return $this->getJsonResponse(
                $returnArray
            );
        }
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSeller($id)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            $returnArray = [];
            $collection = $this->_sellerlistCollectionFactory
            ->create()
            ->addFieldToSelect(
                '*'
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $id]
            )
            ->addFieldToFilter(
                'is_seller',
                ['eq' => 1]
            );
            if ($collection->getSize() > 0) {
                $model = $collection->getLastItem();
                $returnArray = $model->toArray();
                $returnArray['status'] = 1;
                return $this->getJsonResponse(
                    $returnArray
                );
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 2;
            return $this->getJsonResponse(
                $returnArray
            );
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['error'] = __($e->getMessage());
            $returnArray['status'] = 0;
            return $this->getJsonResponse(
                $returnArray
            );
        }
    }

    /**
     * get seller products.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerProducts($id)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            $returnArray = [];
            $collection = $this->mpProductFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $id]
            )
            ->addFieldToFilter(
                'status',
                ['eq' => 1]
            )
            ->setOrder('mageproduct_id');
            if ($collection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('no result found')
                );
            } else {
                $returnArray = $collection->toArray();
                $returnArray['status'] = 1;
                return $this->getJsonResponse(
                    $returnArray
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 2;
            return $this->getJsonResponse(
                $returnArray
            );
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['error'] = __('invalid request');
            $returnArray['status'] = 0;
            return $this->getJsonResponse(
                $returnArray
            );
        }
    }
    //Deprecated
    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerOrders($id)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            $returnArray = [];
            $collection = $this->saleslistFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $id]
            );
            if ($collection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('no result found')
                );
            } else {
                $returnArray = $collection->toArray();
                $returnArray['status'] = 1;
                return $this->getJsonResponse(
                    $returnArray
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 2;
            return $this->getJsonResponse(
                $returnArray
            );
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['error'] = __('invalid request');
            $returnArray['status'] = 0;
            return $this->getJsonResponse(
                $returnArray
            );
        }
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerSalesDetails($id)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            $returnArray = [];
            $collectionOrders = $this->saleslistFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $id]
            )
            ->addFieldToFilter(
                'magequantity',
                ['neq' => 0]
            )
            ->addFieldToSelect('order_id')
            ->distinct(true);
            $collection = $this->mpOrdersFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['in' => $collectionOrders->getData()]
            )->addFieldToFilter(
                'seller_id',
                ['eq' => $id]
            );
            $collection->setOrder(
                'entity_id',
                'desc'
            );
            if ($collection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('no result found')
                );
            } else {
                $returnArray = $collection->toArray();
                $returnArray['status'] = 1;
                return $this->getJsonResponse(
                    $returnArray
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 2;
            return $this->getJsonResponse(
                $returnArray
            );
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['error'] = __('invalid request');
            $returnArray['status'] = 0;
            return $this->getJsonResponse(
                $returnArray
            );
        }
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id      seller id
     * @param int $orderId order id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function createInvoice($id, $orderId)
    {
        $returnArray = [];
        try {
            $sellerId = $id;
            if (!$this->isSeller($sellerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }

            $order = $this->getOrder($orderId);

            $orderId = $order->getId();
            if ($order->canUnhold()) {
                $returnArray['message'] = __('Can not create invoice as order is in HOLD state');
                $returnArray['status'] = self::LOCAL_ERROR;
            } else {
                $data = [];
                $data['send_email'] = 1;
                $marketplaceOrder = $this->mpOrdersFactory->create();
                $model = $marketplaceOrder
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
                    $marketplaceOrder = $tracking;
                }

                $invoiceId = $marketplaceOrder->getInvoiceId();

                if (!$invoiceId) {
                    $items = [];
                    $itemsarray = [];
                    $shippingAmount = 0;
                    $codcharges = 0;
                    $paymentCode = '';
                    $paymentMethod = '';
                    if ($order->getPayment()) {
                        $paymentCode = $order->getPayment()->getMethod();
                    }
                    $trackingsdata = $this->mpOrdersFactory->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'order_id',
                                $orderId
                            )
                            ->addFieldToFilter(
                                'seller_id',
                                $sellerId
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
                    if ($collection->getSize() == 0) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('you are not authorize to create invoice')
                        );
                    }
                    foreach ($collection as $saleproduct) {
                        if ($paymentCode == 'mpcashondelivery') {
                            $codCharges = $codCharges + $saleproduct->getCodCharges();
                        }
                        $tax = $tax + $saleproduct->getTotalTax();
                        array_push($items, $saleproduct['order_item_id']);
                    }

                    $itemsarray = $this->_getItemQtys($order, $items);

                    if (count($itemsarray) > 0 && $order->canInvoice()) {
                        $invoice = $this->invoiceService->prepareInvoice($order, $itemsarray['data']);
                        if (!$invoice) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('We can\'t save the invoice right now.')
                            );
                        }
                        if (!$invoice->getTotalQty()) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('You can\'t create an invoice without products.')
                            );
                        }

                        if (!empty($data['capture_case'])) {
                            $invoice->setRequestedCaptureCase(
                                $data['capture_case']
                            );
                        }

                        if (!empty($data['comment_text'])) {
                            $invoice->addComment(
                                $data['comment_text'],
                                isset($data['comment_customer_notify']),
                                isset($data['is_visible_on_front'])
                            );

                            $invoice->setCustomerNote($data['comment_text']);
                            $invoice->setCustomerNoteNotify(
                                isset($data['comment_customer_notify'])
                            );
                        }

                        $invoice->setShippingAmount($shippingAmount);
                        $invoice->setBaseShippingInclTax($shippingAmount);
                        $invoice->setBaseShippingAmount($shippingAmount);
                        $invoice->setSubtotal($itemsarray['subtotal']);
                        $invoice->setBaseSubtotal($itemsarray['baseSubtotal']);
                        if ($paymentCode == 'mpcashondelivery') {
                            $invoice->setMpcashondelivery($codCharges);
                        }
                        $invoice->setGrandTotal(
                            $itemsarray['subtotal'] +
                                    $shippingAmount +
                                    $codcharges +
                                    $tax
                        );
                        $invoice->setBaseGrandTotal(
                            $itemsarray['subtotal'] + $shippingAmount + $codcharges + $tax
                        );

                        $invoice->register();

                        $invoice->getOrder()->setCustomerNoteNotify(
                            !empty($data['send_email'])
                        );
                        $invoice->getOrder()->setIsInProcess(true);

                        $transactionSave = $this->dbTransaction->addObject(
                            $invoice
                        )->addObject(
                            $invoice->getOrder()
                        );
                        $transactionSave->save();

                        $invoiceId = $invoice->getId();

                        $this->_invoiceSender->send($invoice);
                        $returnArray['invoice_id'] = $invoiceId;
                        $returnArray['message'] = __('Invoice has been created for this order.');
                        $returnArray['status'] = self::SUCCESS;
                    } else {
                        $returnArray['message'] = __('You cannot create invoice for this order.');
                        $returnArray['status'] = self::LOCAL_ERROR;
                    }
                            /*update mpcod table records*/
                    if ($invoiceId != '') {
                        if ($paymentCode == 'mpcashondelivery') {
                            $saleslistColl = $this->saleslistFactory->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'order_id',
                                $orderId
                            )
                            ->addFieldToFilter(
                                'seller_id',
                                $sellerId
                            );
                            foreach ($saleslistColl as $saleslist) {
                                $saleslist->setCollectCodStatus(1);
                                $saleslist->save();
                            }
                        }

                        $trackingcol1 = $this->mpOrdersFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                        foreach ($trackingcol1 as $row) {
                            $row->setInvoiceId($invoiceId);
                            $row->save();
                        }
                    }
                } else {
                    $returnArray['message'] = __('Cannot create Invoice for this order.');
                    $returnArray['status'] = 2;
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = __($e->getMessage());
            $returnArray['status'] = 2;
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['message'] = __($e->getMessage());
            $returnArray['status'] = 0;
        }
        return $this->getJsonResponse($returnArray);
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id        seller id
     * @param int $orderId
     * @param int $invoiceId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function viewInvoice($id, $orderId, $invoiceId)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            $returnArray = [];

            $customerId = $id;
            $invoiceId = $invoiceId;
            $helper = $this->_marketplaceHelper;
            $order = $this->getOrder($orderId);
            $invoice = $this->invoiceFactory
                ->create()
                ->load($invoiceId);
            $paymentCode = '';
            $payment_method = '';
            $orderId = $order->getId();
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
                $payment_method = $order->getPayment()->getConfigData('title');
            }
            $invoiceStatus = '';
            if ($invoice->getState() == 1) {
                $invoiceStatus = __('Pending');
            } elseif ($invoice->getState() == 2) {
                $invoiceStatus = __('Paid');
            } elseif ($invoice->getState() == 3) {
                $invoiceStatus = __('Canceled');
            }
            $marketplaceOrders = $this->mpOrdersFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('seller_id', $customerId);

            if (count($marketplaceOrders)) {
                $returnArray['mainHeading'] = __('View Invoice Details');
                $returnArray['sendmailAction'] = __('Send Email To Customer');
                $returnArray['sendmailWarning'] = __('Are you sure you want to send order email to customer?');
                $returnArray['subHeading'] = __('Invoice #%1 - %2 | %3', $invoice->getIncrementId(), $invoiceStatus, $invoice->getCreatedAtStoreDate());
                $returnArray['orderData']['title'] = __('Order Information');
                $returnArray['orderData']['label'] = __('Order # %1', $order->getIncrementId());
                $returnArray['orderData']['statusLabel'] = __('Order Status');
                $returnArray['orderData']['statusValue'] = ucfirst($order->getStatus());
                $returnArray['orderData']['dateLabel'] = __('Order Date');
                $returnArray['orderData']['dateValue'] = $order->getCreatedAt();

                    // Buyer Data
                    $returnArray['buyerData']['title'] = __('Buyer Information');
                $returnArray['buyerData']['nameLabel'] = __('Customer Name').': ';
                $returnArray['buyerData']['nameValue'] = $order->getCustomerName();
                $returnArray['buyerData']['emailLabel'] = __('Email').': ';
                $returnArray['buyerData']['emailValue'] = $order->getCustomerEmail();

                    // Shipping Address Data
                if (!$order->getIsVirtual()) {
                    $returnArray['shippingAddressData']['title'] = __('Shipping Address');
                    $shippingAddress = $order->getShippingAddress();
                    $shippingAddressData['name'] = $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
                    $shippingAddressData['street'] = $shippingAddress->getStreet()[0];
                    if (count($shippingAddress->getStreet()) > 1) {
                        if ($shippingAddress->getStreet()[1]) {
                            $shippingAddressData['street'] .= $shippingAddress->getStreet()[1];
                        }
                    }
                    $shippingAddressData['state'] = $shippingAddress->getCity().', '.$shippingAddress->getRegion().', '.$shippingAddress->getPostcode();
                    $shippingAddressData['country'] = $this->_country->create()
                    ->load($shippingAddress->getCountryId())->getName();
                    $shippingAddressData['telephone'] = 'T: '.$shippingAddress->getTelephone();
                    $returnArray['shippingAddressData']['address'][] = $shippingAddressData;

                    // Shipping Method Data
                    $returnArray['shippingMethodData']['title'] = __('Shipping Information');
                    if ($order->getShippingDescription()) {
                        $returnArray['shippingMethodData']['method'] = strip_tags($order->getShippingDescription());
                    } else {
                        $returnArray['shippingMethodData']['method'] = __('No shipping information available');
                    }
                }

                    // Billing Address Data
                    $returnArray['billingAddressData']['title'] = __('Billing Address');
                $billingAddress = $order->getBillingAddress();
                $billingAddressData['name'] = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
                $billingAddressData['street'] = $billingAddress->getStreet()[0];
                if (count($billingAddress->getStreet()) > 1) {
                    if ($billingAddress->getStreet()[1]) {
                        $billingAddressData['street'] .= $billingAddress->getStreet()[1];
                    }
                }
                $billingAddressData['state'] = $billingAddress->getCity().', '.$billingAddress->getRegion().', '.$billingAddress->getPostcode();
                $billingAddressData['country'] = $this->_country->create()->load($billingAddress->getCountryId())->getName();
                $billingAddressData['telephone'] = 'T: '.$billingAddress->getTelephone();
                $returnArray['billingAddressData']['address'][] = $billingAddressData;

                    // Payment Method Data
                    $returnArray['paymentMethodData']['title'] = __('Payment Method');
                $returnArray['paymentMethodData']['method'] = $order->getPayment()->getMethodInstance()->getTitle();

                    // Item List
                    $itemCollection = $order->getAllVisibleItems();
                $_count = count($itemCollection);
                $subtotal = 0;
                $vendorSubtotal = 0;
                $totaltax = 0;
                $adminSubtotal = 0;
                $shippingamount = 0;
                $codchargesTotal = 0;

                foreach ($itemCollection as $_item) {
                    $eachItem = [];
                    $rowTotal = 0;
                    $availableSellerItem = 0;
                    $shippingcharges = 0;
                    $itemPrice = 0;
                    $sellerItemCost = 0;
                    $totaltaxPeritem = 0;
                    $codchargesPeritem = 0;
                    $sellerItemCommission = 0;
                    $sellerOrderslist = $this->saleslistFactory
                        ->create()->getCollection()
                        ->addFieldToFilter('seller_id', $customerId)
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('mageproduct_id', $_item->getProductId())
                        ->addFieldToFilter('order_item_id', $_item->getItemId())
                        ->setOrder('order_id', 'DESC');

                    foreach ($sellerOrderslist as $sellerItem) {
                        $availableSellerItem = 1;
                        $totalamount = $sellerItem->getTotalAmount();
                        $sellerItemCost = $sellerItem->getActualSellerAmount();
                        $sellerItemCommission = $sellerItem->getTotalCommision();
                        $shippingcharges = $sellerItem->getShippingCharges();
                        $itemPrice = $sellerItem->getMageproPrice();
                        $totaltaxPeritem = $sellerItem->getTotalTax();
                        $codchargesPeritem = $sellerItem->getCodCharges();
                    }
                    if ($availableSellerItem == 1) {
                        $sellerItemQty = $_item->getQtyOrdered();
                        $rowTotal = $itemPrice * $sellerItemQty;
                        $vendorSubtotal = $vendorSubtotal + $sellerItemCost;
                        $subtotal = $subtotal + $rowTotal;
                        $adminSubtotal = $adminSubtotal + $sellerItemCommission;
                        $totaltax = $totaltax + $totaltaxPeritem;
                        $codchargesTotal = $codchargesTotal + $codchargesPeritem;
                        $shippingamount = $shippingamount + $shippingcharges;
                        $result = [];
                        if ($options = $_item->getProductOptions()) {
                            if (isset($options['options'])) {
                                $result = array_merge($result, $options['options']);
                            }
                            if (isset($options['additional_options'])) {
                                $result = array_merge($result, $options['additional_options']);
                            }
                            if (isset($options['attributes_info'])) {
                                $result = array_merge($result, $options['attributes_info']);
                            }
                        }
                        $eachItem['productName'] = $_item->getName();
                        if ($_options = $result) {
                            foreach ($_options as $_option) {
                                $eachOption = [];
                                $eachOption['label'] = strip_tags($_option['label']);
                                $eachOption['value'] = $_option['value'];
                                $eachItem['option'][] = $eachOption;
                            }
                        }
                        $eachItem['price'] = strip_tags($order->formatPrice($_item->getPrice()));
                        $eachItem['qty']['Ordered'] = $_item->getQtyOrdered() * 1;
                        $eachItem['qty']['Invoiced'] = $_item->getQtyInvoiced() * 1;
                        $eachItem['qty']['Shipped'] = $_item->getQtyShipped() * 1;
                        $eachItem['qty']['Canceled'] = $_item->getQtyCanceled() * 1;
                        $eachItem['qty']['Refunded'] = $_item->getQtyRefunded() * 1;
                        $eachItem['subTotal'] = strip_tags($order->formatPrice($rowTotal));
                        if ($paymentCode == 'mpcashondelivery') {
                            $eachItem['codCharges'] = strip_tags($order->formatPrice($codchargesPeritem));
                        }
                        $eachItem['adminComission'] = strip_tags($order->formatPrice($sellerItemCommission));
                        $eachItem['vendorTotal'] = strip_tags($order->formatPrice($sellerItemCost));
                        $returnArray['items'][] = $eachItem;
                    }
                }
                $returnArray['subtotal']['title'] = __('Subtotal');
                $returnArray['subtotal']['value'] = strip_tags($order->formatPrice($subtotal));
                $returnArray['shipping']['title'] = __('Shipping & Handling');
                $returnArray['shipping']['value'] = strip_tags($order->formatPrice($shippingamount));
                $returnArray['tax']['title'] = __('Total Tax');
                $returnArray['tax']['value'] = strip_tags($order->formatPrice($totaltax));
                $admintotaltax = 0;
                $vendortotaltax = 0;
                if (!$this->_marketplaceHelper->getConfigTaxManage()) {
                    $admintotaltax = $totaltax;
                } else {
                    $vendortotaltax = $totaltax;
                }
                if ($paymentCode == 'mpcashondelivery') {
                    $returnArray['cod']['title'] = __('Total COD Charges');
                    $returnArray['cod']['value'] = strip_tags($order->formatPrice($codchargesTotal));
                }
                $returnArray['totalOrderedAmount']['title'] = __('Total Ordered Amount');
                $returnArray['totalOrderedAmount']['value'] = strip_tags($order->formatPrice($subtotal + $shippingamount + $codchargesTotal + $totaltax));
                $returnArray['totalVendorAmount']['title'] = __('Total Vendor Amount');
                $returnArray['totalVendorAmount']['value'] = strip_tags($order->formatPrice($vendorSubtotal + $shippingamount + $codchargesTotal + $vendortotaltax));
                $returnArray['totalAdminComission']['title'] = __('Total Admin Commission');
                $returnArray['totalAdminComission']['value'] = strip_tags($order->formatPrice($adminSubtotal + $admintotaltax));
                $returnArray['status'] = 1;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid request')
                );
            }

            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = __($e->getMessage());
            $returnArray['status'] = 2;
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = 0;
            $returnArray['message'] = __('invalid request');

            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id      seller id
     * @param int $orderId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function cancelOrder($id, $orderId)
    {
        $returnArray = [];
        $customerId = $id;
        $order = $this->getOrder($orderId);

        $orderId = $order->getId();
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            $orderHelper = $this->orderHelper;
            $flag = $orderHelper->cancelorder($order, $customerId);
            if ($flag) {
                $collection = $this->saleslistFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('seller_id', $customerId)
                        ->addFieldToFilter('order_id', $orderId);
                if ($collection->getSize() == 0) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('invalid request')
                    );
                }
                foreach ($collection as $saleproduct) {
                    $saleproduct->setCpprostatus(2);
                    $saleproduct->setPaidStatus(2);
                    $saleproduct->save();
                    $trackingcoll = $this->mpOrdersFactory
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter('order_id', $orderId)
                            ->addFieldToFilter('seller_id', $customerId);
                    foreach ($trackingcoll as $tracking) {
                        $tracking->setTrackingNumber('canceled');
                        $tracking->setCarrierName('canceled');
                        $tracking->setIsCanceled(1);
                        $tracking->save();
                    }
                }
                $returnArray['message'] = __('The order has been cancelled.');
                $returnArray['status'] = self::SUCCESS;
            } else {
                $returnArray['message'] = __('You are not permitted to cancel this order.');
                $returnArray['status'] = self::LOCAL_ERROR;
            }
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = __($e->getMessage());
            $returnArray['status'] = self::LOCAL_ERROR;

            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['message'] = __('The order has not been cancelled.');
            $returnArray['status'] = self::SEVERE_ERROR;

            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id        seller id
     * @param int $orderId
     * @param int $invoiceId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function createCreditmemo($id, $invoiceId, $orderId)
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
                $creditmemo = $this->_initOrderCreditmemo($sellerId, $invoiceId, $order);
                if ($creditmemo) {
                    if (!$creditmemo->isValidGrandTotal()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The credit memo\'s total must be positive.')
                        );
                    }
                    $data = $this->_request->getPostValue();
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
                    $returnArray['status'] = self::SUCCESS;
                    $returnArray['message'] = __('You created the credit memo.');
                    return $this->getJsonResponse($returnArray);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $returnArray['status'] = self::LOCAL_ERROR;
                $returnArray['message'] = __($e->getMessage());
                return $this->getJsonResponse($returnArray);
            } catch (\Exception $e) {
                $this->createLog($e);
                $returnArray['status'] = self::SEVERE_ERROR;
                $returnArray['message'] = __($e->getMessage());
                return $this->getJsonResponse($returnArray);
            }
        } else {
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('invalid request');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id           seller id
     * @param int $orderId
     * @param int $creditmemoId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function viewCreditmemo($id, $orderId, $creditmemoId)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            $returnArray = [];
            $customerId = $id;
            $creditmemoId = $creditmemoId;

            $helper = $this->_marketplaceHelper;
            $order = $this->getOrder($orderId);
            $paymentCode = '';
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
            }
            $orderId = $order->getId();
            $creditmemo = $this->creditMemoModal
                ->load($creditmemoId);

            $creditmemoStatus = '';
            if ($creditmemo->getState() == 1) {
                $creditmemoStatus = __('Pending');
            } elseif ($creditmemo->getState() == 2) {
                $creditmemoStatus = __('Refunded');
            } elseif ($creditmemo->getState() == 3) {
                $creditmemoStatus = __('Canceled');
            }
            $marketplaceOrders = $this->mpOrdersFactory->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('seller_id', $customerId);
            $tracking = new \Magento\Framework\DataObject();
            foreach ($marketplaceOrders as $tracking) {
                $tracking = $tracking;
            }
            if (count($marketplaceOrders)) {
                $returnArray['sendmailAction'] = __('Send Email To Customer');
                $returnArray['sendmailWarning'] = __('Are you sure you want to send order email to customer?');
                $returnArray['mainHeading'] = __('Credit Memo Information');
                $returnArray['subHeading'] = __('Credit Memo #%1 - %2 | %s', $creditmemo->getIncrementId(), $creditmemoStatus, $creditmemo->getCreatedAtStoreDate());
                $returnArray['orderData']['title'] = __('Order Information');
                $returnArray['orderData']['label'] = __('Order # %1', $order->getIncrementId());
                $returnArray['orderData']['statusLabel'] = __('Order Status');
                $returnArray['orderData']['statusValue'] = ucfirst($order->getStatus());
                $returnArray['orderData']['dateLabel'] = __('Order Date');
                $returnArray['orderData']['dateValue'] = $order->getCreatedAt();

                    // Buyer Data
                    $returnArray['buyerData']['title'] = __('Buyer Information');
                $returnArray['buyerData']['nameLabel'] = __('Customer Name').': ';
                $returnArray['buyerData']['nameValue'] = $order->getCustomerName();
                $returnArray['buyerData']['emailLabel'] = __('Email').': ';
                $returnArray['buyerData']['emailValue'] = $order->getCustomerEmail();

                    // Shipping Address Data
                if (!$order->getIsVirtual()) {
                    $returnArray['shippingAddressData']['title'] = __('Shipping Address');
                    $shippingAddress = $order->getShippingAddress();
                    $shippingAddressData['name'] = $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
                    $shippingAddressData['street'] = $shippingAddress->getStreet()[0];
                    if (count($shippingAddress->getStreet()) > 1) {
                        if ($shippingAddress->getStreet()[1]) {
                            $shippingAddressData['street'] .= $shippingAddress->getStreet()[1];
                        }
                    }
                    $shippingAddressData['state'] = $shippingAddress->getCity().', '.$shippingAddress->getRegion().', '.$shippingAddress->getPostcode();
                    $shippingAddressData['country'] = $this->_country->create()
                    ->load($shippingAddress->getCountryId())->getName();
                    $shippingAddressData['telephone'] = 'T: '.$shippingAddress->getTelephone();
                    $returnArray['shippingAddressData']['address'][] = $shippingAddressData;

                    // Shipping Method Data
                    $returnArray['shippingMethodData']['title'] = __('Shipping Information');
                    if ($order->getShippingDescription()) {
                        $returnArray['shippingMethodData']['method'] = strip_tags($order->getShippingDescription());
                    } else {
                        $returnArray['shippingMethodData']['method'] = __('No shipping information available');
                    }
                }

                    // Billing Address Data
                    $returnArray['billingAddressData']['title'] = __('Billing Address');
                $billingAddress = $order->getBillingAddress();
                $billingAddressData['name'] = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
                $billingAddressData['street'] = $billingAddress->getStreet()[0];
                if (count($billingAddress->getStreet()) > 1) {
                    if ($billingAddress->getStreet()[1]) {
                        $billingAddressData['street'] .= $billingAddress->getStreet()[1];
                    }
                }
                $billingAddressData['state'] = $billingAddress->getCity().', '.$billingAddress->getRegion().', '.$billingAddress->getPostcode();
                $billingAddressData['country'] = $this->_country->create()->load($billingAddress->getCountryId())->getName();
                $billingAddressData['telephone'] = 'T: '.$billingAddress->getTelephone();
                $returnArray['billingAddressData']['address'][] = $billingAddressData;

                    // Payment Method Data
                    $returnArray['paymentMethodData']['title'] = __('Payment Method');
                $returnArray['paymentMethodData']['method'] = $order->getPayment()->getMethodInstance()->getTitle();

                    // Item List
                    $itemCollection = $order->getAllVisibleItems();
                $_count = count($itemCollection);
                $subtotal = 0;
                $vendorSubtotal = 0;
                $totaltax = 0;
                $adminSubtotal = 0;
                $shippingamount = 0;
                $codchargesTotal = 0;
                foreach ($itemCollection as $_item) {
                    $eachItem = [];
                    $rowTotal = 0;
                    $availableSellerItem = 0;
                    $shippingcharges = 0;
                    $itemPrice = 0;
                    $sellerItemCost = 0;
                    $totaltaxPeritem = 0;
                    $codchargesPeritem = 0;
                    $sellerItemCommission = 0;
                    $sellerOrderslist = $this->saleslistFactory
                        ->create()->getCollection()
                        ->addFieldToFilter('seller_id', $customerId)
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('mageproduct_id', $_item->getProductId())
                        ->addFieldToFilter('order_item_id', $_item->getItemId())
                        ->setOrder('order_id', 'DESC');

                    foreach ($sellerOrderslist as $sellerItem) {
                        $availableSellerItem = 1;
                        $totalamount = $sellerItem->getTotalAmount();
                        $sellerItemCost = $sellerItem->getActualSellerAmount();
                        $sellerItemCommission = $sellerItem->getTotalCommision();
                        $shippingcharges = $sellerItem->getShippingCharges();
                        $itemPrice = $sellerItem->getMageproPrice();
                        $totaltaxPeritem = $sellerItem->getTotalTax();
                        $codchargesPeritem = $sellerItem->getCodCharges();
                    }
                    if ($availableSellerItem == 1) {
                        $sellerItemQty = $_item->getQtyOrdered();
                        $rowTotal = $itemPrice * $sellerItemQty;
                        $vendorSubtotal = $vendorSubtotal + $sellerItemCost;
                        $subtotal = $subtotal + $rowTotal;
                        $adminSubtotal = $adminSubtotal + $sellerItemCommission;
                        $totaltax = $totaltax + $totaltaxPeritem;
                        $codchargesTotal = $codchargesTotal + $codchargesPeritem;
                        $shippingamount = $shippingamount + $shippingcharges;
                        $result = [];
                        if ($options = $_item->getProductOptions()) {
                            if (isset($options['options'])) {
                                $result = array_merge($result, $options['options']);
                            }
                            if (isset($options['additional_options'])) {
                                $result = array_merge($result, $options['additional_options']);
                            }
                            if (isset($options['attributes_info'])) {
                                $result = array_merge($result, $options['attributes_info']);
                            }
                        }
                        $eachItem['productName'] = $_item->getName();
                        if ($_options = $result) {
                            foreach ($_options as $_option) {
                                $eachOption = [];
                                $eachOption['label'] = strip_tags($_option['label']);
                                $eachOption['value'] = $_option['value'];
                                $eachItem['option'][] = $eachOption;
                            }
                        }
                        $eachItem['price'] = strip_tags($order->formatPrice($_item->getPrice()));
                        $eachItem['qty']['Ordered'] = $_item->getQtyOrdered() * 1;
                        $eachItem['qty']['Invoiced'] = $_item->getQtyInvoiced() * 1;
                        $eachItem['qty']['Shipped'] = $_item->getQtyShipped() * 1;
                        $eachItem['qty']['Canceled'] = $_item->getQtyCanceled() * 1;
                        $eachItem['qty']['Refunded'] = $_item->getQtyRefunded() * 1;
                        $eachItem['subTotal'] = strip_tags($order->formatPrice($rowTotal));
                        if ($paymentCode == 'mpcashondelivery') {
                            $eachItem['codCharges'] = strip_tags($order->formatPrice($codchargesPeritem));
                        }
                        $eachItem['adminComission'] = strip_tags($order->formatPrice($sellerItemCommission));
                        $eachItem['vendorTotal'] = strip_tags($order->formatPrice($sellerItemCost));
                        $returnArray['items'][] = $eachItem;
                    }
                }
                $returnArray['subtotal']['title'] = __('Subtotal');
                $returnArray['subtotal']['value'] = strip_tags($order->formatPrice($subtotal));
                $returnArray['shipping']['title'] = __('Shipping & Handling');
                $returnArray['shipping']['value'] = strip_tags($order->formatPrice($shippingamount));
                $returnArray['tax']['title'] = __('Total Tax');
                $returnArray['tax']['value'] = strip_tags($order->formatPrice($totaltax));
                $admintotaltax = 0;
                $vendortotaltax = 0;
                if (!$this->_marketplaceHelper->getConfigTaxManage()) {
                    $admintotaltax = $totaltax;
                } else {
                    $vendortotaltax = $totaltax;
                }
                if ($paymentCode == 'mpcashondelivery') {
                    $returnArray['cod']['title'] = __('Total COD Charges');
                    $returnArray['cod']['value'] = strip_tags($order->formatPrice($codchargesTotal));
                }
                $returnArray['totalOrderedAmount']['title'] = __('Total Ordered Amount');
                $returnArray['totalOrderedAmount']['value'] = strip_tags($order->formatPrice($subtotal + $shippingamount + $codchargesTotal + $totaltax));
                $returnArray['totalVendorAmount']['title'] = __('Total Vendor Amount');
                $returnArray['totalVendorAmount']['value'] = strip_tags($order->formatPrice($vendorSubtotal + $shippingamount + $codchargesTotal + $vendortotaltax));
                $returnArray['totalAdminComission']['title'] = __('Total Admin Commission');
                $returnArray['totalAdminComission']['value'] = strip_tags($order->formatPrice($adminSubtotal + $admintotaltax));
            }

            $returnArray['status'] = self::SUCCESS;

            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['status'] = self::LOCAL_ERROR;
            $returnArray['message'] = __($e->getMessage());
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['success'] = 0;
            $returnArray['message'] = __($e->getMessage());

            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id
     * @param int $orderId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function ship($id, $orderId)
    {
        $status = 0;
        $returnArray = [];
        if (!$this->isSeller($id)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('invalid seller')
            );
        }
        $paramData = $this->_request->getPostValue();
        if (!isset($paramData['tracking_number'])) {
            return $this->getJsonResponse(['status' => $status, 'message' => 'Invalid parameters.']);
        }

        $message = '';
        if ($order = $this->_initOrder($orderId, $id)) {
            $sellerId = $id;
            $marketplaceOrder = $this->getOrderinfo($id, $sellerId);
            $trackingid = '';
            $carrier = '';
            $trackingData = [];
            try {
                if (!empty($paramData['tracking_number'])) {
                    $trackingid = $paramData['tracking_number'];
                    $trackingData[1]['number'] = $trackingid;
                    $trackingData[1]['carrier_code'] = 'custom';
                }
                if (!empty($paramData['carrier'])) {
                    $carrier = $paramData['carrier'];
                    $trackingData[1]['title'] = $carrier;
                }

                if (!empty($paramData['api_shipment'])) {
                    $this->_eventManager->dispatch(
                        'generate_api_shipment',
                        [
                            'api_shipment' => $paramData['api_shipment'],
                            'order_id' => $orderId,
                        ]
                    );
                    $shipmentData = $this->_customerSession->getData('shipment_data');
                    $apiName = $shipmentData['api_name'];
                    $trackingid = $shipmentData['tracking_number'];
                    $trackingData[1]['number'] = $trackingid;
                    $trackingData[1]['carrier_code'] = 'custom';
                    $this->_customerSession->unsetData('shipment_data');
                }
                if (empty($paramData['api_shipment']) || $trackingid != '') {
                    if ($order->canUnhold()) {
                        return $this->getJsonResponse(['status' => $status,'message' => 'Can not create shipment as order is in HOLD state']);
                    } else {
                        $items = [];
                        $shippingAmount = 0;

                        $trackingsdata = $this->mpOrdersFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                        foreach ($trackingsdata as $tracking) {
                            $shippingAmount = $tracking->getShippingCharges();
                        }

                        $collection = $this->saleslistFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                        foreach ($collection as $saleproduct) {
                            array_push($items, $saleproduct['order_item_id']);
                        }

                        $itemsarray = $this->_getShippingItemQtys($order, $items);

                        if (count($itemsarray) > 0) {
                            $shipment = false;
                            $shipmentId = 0;
                            if (!empty($paramData['shipment_id'])) {
                                $shipmentId = $paramData['shipment_id'];
                            }
                            if ($shipmentId) {
                                $shipment = $this->_shipmentFactory->create()->load($shipmentId);
                            } elseif ($orderId) {
                                if ($order->getForcedDoShipmentWithInvoice()) {
                                    return $this->getJsonResponse(['status'=> $status,'message' => 'Cannot do shipment for the order separately from invoice.']);
                                }
                                if (!$order->canShip()) {
                                    return $this->getJsonResponse(['status' => $status ,'message' => 'Cannot do shipment for the order.']);
                                }

                                $shipment = $this->_prepareShipment(
                                    $order,
                                    $itemsarray['data'],
                                    $trackingData
                                );
                            }
                            if ($shipment) {
                                $comment = '';
                                $shipment->getOrder()->setCustomerNoteNotify(
                                    !empty($data['send_email'])
                                );
                                $shippingLabel = '';
                                if (!empty($data['create_shipping_label'])) {
                                    $shippingLabel = $data['create_shipping_label'];
                                }
                                $isNeedCreateLabel=!empty($shippingLabel) && $shippingLabel;
                                $shipment->getOrder()->setIsInProcess(true);

                                $transactionSave = $this->dbTransaction->addObject(
                                    $shipment
                                )->addObject(
                                    $shipment->getOrder()
                                );
                                $transactionSave->save();

                                $shipmentId = $shipment->getId();

                                $courrier = 'custom';
                                $sellerCollection = $this->mpOrdersFactory->create()
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
                                        $row->setTrackingNumber($trackingid);
                                        $row->setCarrierName($carrier);
                                        $row->save();
                                    }
                                }

                                $this->_shipmentSender->send($shipment);

                                $shipmentCreatedMessage = __('The shipment has been created.');
                                $labelMessage = __('The shipping label has been created.');
                                $message = $isNeedCreateLabel ? $shipmentCreatedMessage.' '.$labelMessage
                                    : $shipmentCreatedMessage;
                                $status = 1;
                            }
                        }
                    }
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $status = 0;
                $message = $e->getMessage();
            } catch (\Exception $e) {
                $status = 2;
                $message = $e->getMessage();
            }
            return $this->getJsonResponse(['status' => $status,'message' => $message]);
        } else {
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('invalid request');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * prepare the shipment
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $items
     * @param array $trackingData
     * @return void
     */
    public function _prepareShipment($order, $items, $trackingData)
    {
        $shipment = $this->_shipmentFactory->create(
            $order,
            $items,
            $trackingData
        );

        if (!$shipment->getTotalQty()) {
            return false;
        }

        return $shipment->register();
    }

    /**
     * get the item quantity of shipping
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $items
     * @return array
     */
    public function _getShippingItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)) {
                $data[$item->getItemId()] = intval($item->getQtyOrdered() - $item->getQtyShipped());

                $_item = $item;

                // for bundle product
                $bundleitems = array_merge([$_item], $_item->getChildrenItems());

                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = intval(
                                $_bundleitem->getQtyOrdered() - $item->getQtyShipped()
                            );
                        }
                    }
                }
                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }

        return ['data' => $data,'subtotal' => $subtotal,'baseSubtotal' => $baseSubtotal];
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id         seller id
     * @param int $orderId
     * @param int $shipmentId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function viewShipment($id, $orderId, $shipmentId)
    {
        if (!$this->isSeller($id)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('invalid seller')
            );
        }
        return $this->getJsonResponse(['status' => 'api not available']);
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function mailToAdmin($id)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            $returnArray = [];
            $customerId = $id;
            $subject = $this->_request->getPost('subject');
            $query = $this->_request->getPost('query');

            $helper = $this->_marketplaceHelper;

            $seller = $this->_customerFactory->create()->load($customerId);

            $sellerName = $seller->getName();
            $sellerEmail = $seller->getEmail();

            $adminStoremail = $helper->getAdminEmailId();
            $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
            $adminUsername = 'Admin';

            $emailTemplateVariables = [];
            $senderInfo = [];
            $receiverInfo = [];
            $emailTemplateVariables['myvar1'] = $adminUsername;
            $emailTemplateVariables['myvar2'] = $sellerName;
            $emailTemplateVariables['subject'] = $subject;
            $emailTemplateVariables['myvar3'] = $query;
            $senderInfo = [
                    'name' => $sellerName,
                    'email' => $sellerEmail,
                ];
            $receiverInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];

            $this->emailHelper
                ->askQueryAdminEmail($emailTemplateVariables, $senderInfo, $receiverInfo);

            $returnArray['message'] = __('The message has been sent.');
            $returnArray['status'] = self::SUCCESS;

            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['success'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('invalid request');

            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function mailToSeller($id)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            $returnArray = [];

            $sellerId = $id;
            $subject = $this->_request->getPost('subject');
            $query = $this->_request->getPost('query');
            $productId = $this->_request->getPost('product_id');
            $customerEmail = $this->_request->getPost('customer_email');
            $customerName = $this->_request->getPost('customer_name');

            $seller = $this->_customerFactory->create()->load($sellerId);
            $sellerEmail = $seller->getEmail();
            $sellerName = $seller->getFirstname().' '.$seller->getLastname();
            if (!isset($productId) || $productId == '') {
                $productId = 0;
            }

            $buyerEmail = $customerEmail;
            $buyerName = $customerName;

            if (strlen($buyerName) < 2) {
                $buyerName = 'Guest';
            }
            $emailTemplateVariables = [];
            $emailTemplateVariables['myvar1'] = $sellerName;
            $emailTemplateVariables['myvar3'] = $this->_productFactory
                ->create()->load($productId)->getName();
            $emailTemplateVariables['myvar4'] = $query;
            $emailTemplateVariables['myvar5'] = $buyerEmail;
            $emailTemplateVariables['myvar6'] = $subject;
            $senderInfo = [
                'name' => $buyerName,
                'email' => $buyerEmail,
                ];
            $receiverInfo = [
                    'name' => $seller->getName(),
                    'email' => $sellerEmail,
                ];
            $data['email'] = $customerEmail;
            $data['name'] = $customerName;
            $data['product-id'] = $productId;
            $data['ask'] = $query;
            $data['subject'] = $subject;
            $data['seller-id'] = $sellerId;

            $this->emailHelper->sendQuerypartnerEmail(
                $data,
                $emailTemplateVariables,
                $senderInfo,
                $receiverInfo
            );

            $returnArray['status'] = self::SUCCESS;
            $returnArray['message'] = __('Mail sent successfully !!');

            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('invalid request');

            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * become partner .
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function becomePartner($id)
    {
        try {
            if ($this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('already seller')
                );
            }
            $fields = $this->_request->getPostValue();

            $shop_urlcount = $this->_sellerFactory->create()->getCollection()
                ->addFieldToFilter(
                    'shop_url',
                    $fields['shop_url']
                );
            if (!count($shop_urlcount)) {
                $sellerId = $id;
                $status = $this->_marketplaceHelper->getIsPartnerApproval() ? 0 : 1;
                $model = $this->_sellerFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('shop_url', $fields['shop_url']);
                if (!count($model)) {
                    if (isset($fields['is_seller']) && $fields['is_seller']) {
                        $autoId = 0;
                        $collection = $this->_sellerFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId);
                        foreach ($collection as $value) {
                            $autoId = $value->getId();
                        }
                        $value = $this->_sellerFactory->create()->load($autoId);
                        $value->setData('is_seller', $status);
                        $value->setData('shop_url', $fields['shop_url']);
                        $value->setData('seller_id', $sellerId);
                        $value->setCreatedAt($this->_date->gmtDate());
                        $value->setUpdatedAt($this->_date->gmtDate());
                        $value->save();
                        try {
                            if (!empty($errors)) {
                                foreach ($errors as $message) {
                                    throw new \Magento\Framework\Exception\LocalizedException($message);
                                }
                            } else {
                                $returnArray['message'] = __('Profile information was successfully saved');
                                $returnArray['status'] = self::SUCCESS;
                            }
                        } catch (\Exception $e) {
                            $this->createLog($e);
                            throw new \Magento\Framework\Exception\LocalizedException(
                                $e,
                                __('We can\'t save the customer.')
                            );
                        }
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedExceptionr(
                            __('Please confirm that you want to become seller.')
                        );
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Shop URL already exist please set another.')
                    );
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Shop URL already exist please set another.')
                );
            }
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['status'] = self::LOCAL_ERROR;
            $returnArray['message'] = __($e->getMessage());
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __($e->getMessage());
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * get seller reviews .
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerReviews($id)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            $returnArray = [];
            $reviewCollection = $this->feedbackFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('status', ['neq' => 0])
            ->addFieldToFilter('seller_id', $id);
            if ($reviewCollection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('no reviews yet on this seller')
                );
            }

            $returnArray = $reviewCollection->toArray();

            $returnArray['status'] = self::SUCCESS;
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['status'] = self::LOCAL_ERROR;
            $returnArray['message'] = __($e->getMessage());
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('invalid request');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * make seller reviews .
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function makeSellerReview($id)
    {
        try {
            if (!$this->isSeller($id)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('invalid seller')
                );
            }
            $returnArray = [];
            $data = $this->_request->getPostValue();
            $errors = $this->validateReviewPost($data);
            if (count($errors) > 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(implode(', ', $errors))
                );
            }
            $data['created_at'] = $this->_date->gmtDate();

            $data['seller_id'] = $id;
            if (!isset($data['buyer_id']) || !$data['buyer_id']) {
                $data['buyer_id'] = null;
            }

            if (!isset($data['buyer_email']) && $data['buyer_id']) {
                $data['buyer_email'] = $this->_customerFactory->create()->load($data['buyer_id'])->getEmail();
            }

            $customerId = $data['buyer_id'];
            $sellerId = $id;
            $feedbackcount = 0;
            $collectionfeed = $this->feedbackcountFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('buyer_id', $customerId)
                ->addFieldToFilter('seller_id', $sellerId);

            foreach ($collectionfeed as $value) {
                $feedcountid = $value->getEntityId();
                $ordercount = $value->getOrderCount();
                $feedbackcount = $value->getFeedbackCount();
                $value->setFeedbackCount($feedbackcount + 1);
                $value->save();
            }
            $reviewId = $this->feedbackFactory->create()->setData($data)->save()->getId();
            $returnArray['review_id'] = $reviewId;
            $returnArray['message'] = __('Your review successfully saved');
            $returnArray['status'] = self::SUCCESS;

            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['status'] = self::LOCAL_ERROR;
            $returnArray['message'] = __($e->getMessage());
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('invalid request');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * get review .
     *
     * @api
     *
     * @param int $review_id review id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getReview($review_id)
    {
        try {
            $returnArray = [];
            $reviewCollection = $this->feedbackFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('status', ['neq' => 0])
            ->addFieldToFilter('entity_id', $review_id);
            if ($reviewCollection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('not a valid review id')
                );
            }

            $returnArray = $reviewCollection->toArray();

            $returnArray['status'] = self::SUCCESS;
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['status'] = self::LOCAL_ERROR;
            $returnArray['message'] = __($e->getMessage());
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['status'] = self::SEVERE_ERROR;
            $returnArray['message'] = __('invalid request');
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * validateReviewPost.
     *
     * @param array $data
     *
     * @return array
     */
    protected function validateReviewPost($data)
    {
        $errors = [];
        if (!is_array($data) || count($data) == 0) {
            return ['invalid post data'];
        }
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'feed_price':
                    if (($value % 20 !=0) || ($value > 100 || $value <= 0)) {
                        $errors[] = __('invalid feed price');
                    }
                    break;
                case 'feed_value':
                    if (($value % 20 !=0) || ($value > 100 || $value <= 0)) {
                        $errors[] = __('invalid feed value');
                    }
                    break;
                case 'feed_quality':
                    if (($value % 20 !=0) || ($value > 100 || $value <= 0)) {
                        $errors[] = __('invalid feed quality');
                    }
                    break;
                case 'buyer_email':
                    $emailValidator = new \Zend\Validator\EmailAddress();

                    if (!$emailValidator->isValid($value)) {
                        $errors[] = __('invalid email address');
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * get landing page .
     *
     * @api
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getLandingPageData()
    {
        try {
            $width = 600;
            $returnArray = [];
            $height = $width / 2;
            $allSellers = [];
            $helper = $this->_marketplaceHelper;
            $marketplacelabel1 = $helper->getMarketplacelabel1();
            $marketplacelabel2 = $helper->getMarketplacelabel2();
            $marketplacelabel3 = $helper->getMarketplacelabel3();
            $marketplacelabel4 = $helper->getMarketplacelabel4();
            $bannerDisplay = $helper->getDisplayBanner();
            $bannerImage = $helper->getBannerImage();
            $bannerContent = $helper->getBannerContent();
            $iconsDisplay = $helper->getDisplayIcon();
            $iconImage1 = $helper->getIconImage1();
            $iconImage1Label = $helper->getIconImageLabel1();
            $iconImage2 = $helper->getIconImage2();
            $iconImage2Label = $helper->getIconImageLabel2();
            $iconImage3 = $helper->getIconImage3();
            $iconImage3Label = $helper->getIconImageLabel3();
            $iconImage4 = $helper->getIconImage4();
            $iconImage4Label = $helper->getIconImageLabel4();
            $marketplacebutton = $helper->getMarketplacebutton();
            $marketplaceprofile = $helper->getMarketplaceprofile();
                /*order collection*/
                $sellers_order = $this->mpOrdersFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter(
                    'invoice_id',
                    ['neq' => 0]
                )
                ->addFieldToSelect('seller_id');

            $sellers_order->getSelect()
                ->join(
                    ['ccp' => $this->_resourceConnection->getTableName('marketplace_userdata')],
                    'ccp.seller_id = main_table.seller_id',
                    ['is_seller' => 'is_seller']
                )
                ->where('ccp.is_seller = 1');

            $sellers_order->getSelect()
                ->columns('COUNT(*) as countOrder')->group('seller_id');
            $seller_arr = [];
            foreach ($sellers_order as $value) {
                if ($helper->getSellerProCount($value['seller_id'])) {
                    $seller_arr[$value['seller_id']] = [];
                    $seller_products = $this->saleslistFactory
                        ->create()->getCollection()
                                        ->addFieldToFilter('main_table.seller_id', $value['seller_id'])
                                        ->addFieldToFilter('cpprostatus', 1)
                                        ->addFieldToSelect('mageproduct_id')
                                        ->addFieldToSelect('magequantity');
                    $seller_products->getSelect()
                                        ->columns('SUM(magequantity) as countOrderedProduct')
                                        ->group('main_table.mageproduct_id');
                    $seller_products->getSelect()
                                        ->joinLeft(
                                            ['ccp' => $this->_resourceConnection->getTableName('marketplace_product')],
                                            'ccp.mageproduct_id = main_table.mageproduct_id',
                                            ['status' => 'status']
                                        )->where('ccp.status = 1');

                    $seller_products->setOrder('countOrderedProduct', 'DESC')->setPageSize(3);
                    foreach ($seller_products as $seller_product) {
                        array_push($seller_arr[$value['seller_id']], $seller_product['mageproduct_id']);
                    }
                }
            }
            if (count($seller_arr) != 4) {
                $i = count($seller_arr);
                $count_pro_arr = [];
                $seller_product_coll = $this->mpProductFactory
                        ->create()->getCollection()->addFieldToFilter('status', 1);
                $seller_product_coll
                    ->getSelect()->join(
                        ['ccp' => $this->_resourceConnection->getTableName('marketplace_userdata')],
                        'ccp.seller_id = main_table.seller_id',
                        ['is_seller' => 'is_seller']
                    )
                    ->where('ccp.is_seller = 1');

                $seller_product_coll->getSelect()->columns('COUNT(*) as countOrder')->group('main_table.seller_id');

                foreach ($seller_product_coll as $value) {
                    if (!isset($count_pro_arr[$value['seller_id']])) {
                        $count_pro_arr[$value['seller_id']] = [];
                    }
                    $count_pro_arr[$value['seller_id']] = $value['countOrder'];
                }
                arsort($count_pro_arr);
                foreach ($count_pro_arr as $procount_seller_id => $procount) {
                    if ($i <= 4) {
                        if ($helper->getSellerProCount($procount_seller_id)) {
                            if (!isset($seller_arr[$procount_seller_id])) {
                                $seller_arr[$procount_seller_id] = [];
                            }
                            $seller_product_coll = $this->mpProductFactory
                                    ->create()->getCollection()
                                    ->addFieldToFilter('seller_id', $procount_seller_id)
                                    ->addFieldToFilter('status', 1)
                                    ->setPageSize(3);
                            foreach ($seller_product_coll as $value) {
                                array_push($seller_arr[$procount_seller_id], $value['mageproduct_id']);
                            }
                        }
                    }
                    ++$i;
                }
            }
            if ($bannerDisplay) {
                $bannerImagePath = explode(DS, $bannerImage);

                if (0) {
                    $base_path = $this->directoryList->getPath('media').DS.'marketplace'.DS.'banner'.DS.end($bannerImagePath);
                    $new_path = $this->directoryList->getPath('media').DS.'mpapi'.DS.'marketplace'.DS.'banner'.DS.$width.'x'.$height.DS.end($bannerImagePath);
                    $new_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'mpapi'.DS.'marketplace'.DS.'banner'.DS.$width.'x'.$height.DS.end($bannerImagePath);
                    if (!file_exists($new_path)) {
                        $this->imageUpload(
                            $base_path,
                            $new_path,
                            $width,
                            $height
                        );
                    }
                    $returnArray['bannerImage'] = $new_url;
                }
                $returnArray['banner'][0]['label'] = $marketplacebutton;
                $returnArray['banner'][0]['content'] = strip_tags($bannerContent);
            }
            $returnArray['label1'] = $marketplacelabel1;
            if ($iconsDisplay) {
                $returnArray['icons'][] = ['image' => $iconImage1, 'label' => $iconImage1Label];
                $returnArray['icons'][] = ['image' => $iconImage2, 'label' => $iconImage2Label];
                $returnArray['icons'][] = ['image' => $iconImage3, 'label' => $iconImage3Label];
                $returnArray['icons'][] = ['image' => $iconImage4, 'label' => $iconImage4Label];
            }
            $returnArray['label2'] = $marketplacelabel2;
            $i = 0;
            $count = count($seller_arr);
            $logowidth = $logoheight = $width / 4;
            foreach ($seller_arr as $seller_id => $products) {
                $eachSeller = [];
                ++$i;
                $seller = $this->_customerFactory->create()->load($seller_id);
                $seller_product_count = 0;
                $profileurl = 0;
                $shoptitle = '';
                $logoImage = 'noimage.png';
                $seller_product_count = $helper->getSellerProCount($seller_id);
                $seller_data = $this->_sellerFactory
                        ->create()->getCollection()->addFieldToFilter('seller_id', $seller_id);
                foreach ($seller_data as $seller_data_result) {
                    $profileurl = $seller_data_result->getShopUrl();
                    $shoptitle = $seller_data_result->getShopTitle();
                    $logoImage = $seller_data_result->getlogoPic() == '' ? 'noimage.png' : $seller_data_result->getLogoPic();
                }
                if (!$shoptitle) {
                    $shoptitle = $seller->getName();
                }
                $base_path = $this->directoryList->getPath('media').DS.'avatar'.DS.$logoImage;
                $new_path = $this->directoryList->getPath('media').DS.'mpapi'.DS.'avatar'.DS.$logowidth.'x'.$logoheight.DS.$logoImage;
                $logoUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'mpapi'.DS.'avatar'.DS.$logowidth.'x'.$logoheight.DS.$logoImage;
                if (file_exists($base_path)) {
                    if (!file_exists($new_path)) {
                        $this->imageUpload(
                            $base_path,
                            $new_path,
                            $logowidth,
                            $logoheight
                        );
                    }
                }
                if (!isset($products[0])) {
                    $products[0] = 0;
                    $seller_product_row = $this->mpProductFactory
                                    ->create()->getCollection()
                                        ->addFieldToFilter('seller_id', $seller_id)
                                        ->addFieldToFilter('status', 1);
                    if (isset($products[1])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[1]]);
                    }
                    if (isset($products[2])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[2]]);
                    }
                    $seller_product_row->getSelect()
                                        ->columns('COUNT(*) as countproducts')
                                        ->group('seller_id');
                    foreach ($seller_product_row as $seller_product_row_data) {
                        $products[0] = $seller_product_row_data['mageproduct_id'];
                    }
                }
                if (!isset($products[1])) {
                    $products[1] = 0;
                    $seller_product_row = $this->mpProductFactory
                                    ->create()->getCollection()
                                        ->addFieldToFilter('seller_id', $seller_id)
                                        ->addFieldToFilter('status', 1);
                    if (isset($products[0])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[0]]);
                    }
                    if (isset($products[2])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[2]]);
                    }
                    $seller_product_row->getSelect()
                                        ->columns('COUNT(*) as countproducts')
                                        ->group('seller_id');
                    foreach ($seller_product_row as $seller_product_row_data) {
                        $products[1] = $seller_product_row_data['mageproduct_id'];
                    }
                }
                if (!isset($products[2])) {
                    $products[2] = 0;
                    $seller_product_row = $this->mpProductFactory
                                    ->create()->getCollection()
                                        ->addFieldToFilter('seller_id', $seller_id)
                                        ->addFieldToFilter('status', 1);
                    if (isset($products[1])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[1]]);
                    }
                    if (isset($products[0])) {
                        $seller_product_row->addFieldToFilter('mageproduct_id', ['neq' => $products[0]]);
                    }
                    $seller_product_row->getSelect()
                                        ->columns('COUNT(*) as countproducts')
                                        ->group('seller_id');
                    foreach ($seller_product_row as $seller_product_row_data) {
                        $products[2] = $seller_product_row_data['mageproduct_id'];
                    }
                }
                $product_1 = $this->_productFactory
                                    ->create()->load($products[0]);
                $product_2 = $this->_productFactory
                                    ->create()->load($products[1]);
                $product_3 = $this->_productFactory
                                    ->create()->load($products[2]);

                $eachSeller['pro1id'] = $product_1->getid();
                $eachSeller['pro1name'] = $product_1->getName();
                $eachSeller['pro1type'] = $product_1->getTypeId();

                $eachSeller['pro1thumbnail'] = $this->getImageUrl(
                    $product_1,
                    $width / 2.5,
                    'product_page_image_large'
                );
                $eachSeller['pro2id'] = $product_2->getid();
                $eachSeller['pro2name'] = $product_2->getName();
                $eachSeller['pro2type'] = $product_2->getTypeId();
                $eachSeller['pro2thumbnail'] =
                    $this->getImageUrl(
                        $product_2,
                        $width / 2.5,
                        'product_page_image_large'
                    );

                $eachSeller['pro3id'] = $product_3->getId();
                $eachSeller['pro3name'] = $product_3->getName();
                $eachSeller['pro3type'] = $product_3->getTypeId();
                $eachSeller['pro3thumbnail'] =
                    $this->getImageUrl(
                        $product_3,
                        $width / 2.5,
                        'product_page_image_large'
                    );
                $eachSeller['shopTitle'] = $shoptitle;
                $eachSeller['profileurl'] = $profileurl;
                $eachSeller['sellerIcon'] = $logoUrl;
                $eachSeller['sellerProductCount'] = $seller_product_count;
                $allSellers[] = $eachSeller;
            }
            $returnArray['sellers'] = $allSellers;
            $returnArray['label3'] = $marketplacelabel3;
            $returnArray['label4'] = $marketplacelabel4;
            $returnArray['aboutImage'] = strip_tags($marketplaceprofile);
            
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['success'] = 0;
            $returnArray['message'] = __($e->getMessage());

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

    protected function _getItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)) {
                $data[$item->getItemId()] = intval($item->getQtyOrdered() - $item->getQtyInvoiced());

                $_item = $item;

                // for bundle product
                $bundleitems = array_merge([$_item], $_item->getChildrenItems());

                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = intval(
                                $_bundleitem->getQtyOrdered() - $item->getQtyInvoiced()
                            );
                        }
                    }
                }
                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }

        return ['data' => $data,'subtotal' => $subtotal,'baseSubtotal' => $baseSubtotal];
    }

    /**
     * Get requested items qtys.
     */
    protected function _getItemData($order, $items)
    {
        $refundData = $this->_request->getPostValue();
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
     * Initialize invoice model instance.
     *
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|false
     */
    protected function _initInvoice($invoiceId, $orderId, $sellerId)
    {
        $invoice = false;
        if (!$invoiceId) {
            return false;
        }
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->invoiceRepository->get($invoiceId);
        if (!$invoice) {
            return false;
        }
        try {
            $tracking = null;
            $marketplaceOrder = $this->mpOrdersFactory->create();
            $model = $marketplaceOrder
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
                $marketplaceOrder = $tracking;
            }
            $tracking = $marketplaceOrder;
            if (count($tracking)) {
                if ($tracking->getInvoiceId() == $invoiceId) {
                    if (!$invoiceId) {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->createLog($e);

            return false;
        }

        return $invoice;
    }

    /**
     * Initialize invoice model instance.
     *
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|false
     */
    protected function _initCreditmemo($creditmemoId, $orderId, $sellerId)
    {
        $creditmemo = false;

        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $this->creditmemoRepositoryInterface
        ->get($creditmemoId);
        if (!$creditmemo) {
            return false;
        }
        try {
            $tracking = null;
            $marketplaceOrder = $this->mpOrdersFactory->create();
            $model = $marketplaceOrder
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
                $marketplaceOrder = $tracking;
            }
            $tracking = $marketplaceOrder;

            if (count($tracking)) {
                $creditmemoArr = explode(',', $tracking->getCreditmemoId());
                if (in_array($creditmemoId, $creditmemoArr)) {
                    if (!$creditmemoId) {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->createLog($e);

            return false;
        }

        return $creditmemo;
    }

    /**
     * Initialize shipment model instance.
     *
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _initShipment($orderId, $shipmentId, $sellerId)
    {
        if (!$shipmentId) {
            return false;
        }
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->_shipmentFactory->create()
        ->load($shipmentId);
        if (!$shipment) {
            return false;
        }
        try {
            $order = $this->getOrder($orderId);
            $tracking = $this->getOrderinfo($orderId, $sellerId);
            if (count($tracking)) {
                if ($tracking->getShipmentId() == $shipmentId) {
                    if (!$shipmentId) {
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

        return $shipment;
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
     * Return the Customer seller status.
     *
     * @return bool|0|1
     */
    public function isSeller($id)
    {
        $sellerId = '';
        $sellerStatus = 0;
        $model = $this->_sellerFactory->create()
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
     * @param \Magento\Sales\Model\Order $order
     *
     * @return $this|bool
     */
    protected function _initCreditmemoInvoice($invoiceId, $order)
    {
        if ($invoiceId) {
            $invoice = $this->_invoiceRepository->get($invoiceId);
            $invoice->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }

        return false;
    }

    /**
     * Initialize creditmemo model instance.
     *
     * @return \Magento\Sales\Model\Order\Creditmemo|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _initOrderCreditmemo($sellerId, $invoiceId, $order)
    {
        $refundData = $this->_request->getPostValue();
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

        $savedData = $this->_getItemData($order, $items);
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
            $creditmemo = $this->_creditmemoFactory->createByInvoice(
                $invoice,
                $refundData['creditmemo']
            );
        } else {
            $creditmemo = $this->_creditmemoFactory->createByOrder(
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
     * imageUpload general image upload function.
     *
     * @param string $basePath
     * @param string $newPath
     * @param int    $width
     * @param int    $height
     */
    public function imageUpload($basePath, $newPath, $width, $height)
    {
        $imageObj = $this->imageFactory->create($basePath);
        $imageObj->keepAspectRatio(false);
        $imageObj->backgroundColor([255, 255, 255]);
        $imageObj->keepFrame(false);
        $imageObj->resize($width, $height);
        $imageObj->save($newPath);
    }

    /**
     * getImageUrl get Product Image.
     *
     * @param Magento\Catalog\Model\product $_product
     * @param float                         $resize
     * @param string                        $imageType
     * @param bool                          $keepFrame
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getImageUrl(
        $_product,
        $resize,
        $imageType = 'product_page_image_large',
        $keepFrame = true
    ) {
        return $this->imageHelper
        ->init($_product, $imageType)
        ->keepFrame($keepFrame)
        ->resize($resize)
        ->getUrl();
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
        if (preg_match("/^2\.[0-1]\.\d/", $this->_productMetadata->getVersion())) {
            return $res;
        }
        if (preg_match("/^2\.2\.\d/", $this->_productMetadata->getVersion())) {
            return $res->getData();
        }
        if (preg_match("/^2\.3\.\d/", $this->_productMetadata->getVersion())) {
            return $res->getData();
        }
    }

    public function createLog($object, $info = false)
    {
        $myLogger = $this->_logger;
        if ($info) {
            $myLogger->info($info);
        }
        $myLogger->debug($object);
    }

    /**
     * Interface for managing customers accounts.
     *
     * @api
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function createAccount()
    {
        $params = $this->_request->getPostValue();
        $returnArray = [];
        $email = $params['customer']['email'];
        if (isset($params['customer']['email']) &&
            isset($params['customer']['firstname']) &&
            isset($params['customer']['lastname']) &&
            isset($params['customer']['storeId']) &&
            isset($params['customer']['websiteId']) &&
            isset($params['is_seller']) &&
            isset($params['profileurl']) &&
            isset($params['password']) &&
            isset($params['registered'])
        ) {
            if ((int) $params['registered'] == 1) {
                $customer = $this->_customerFactory->create();
                
                $customer->setWebsiteId($params['customer']['websiteId'])->loadByEmail($email);

                if ($customer->getId()) {
                    $sellerCreated = $this->createSeller($params, $customer);
                    if ($sellerCreated == true && $customer->getId()) {
                        $returnArray['success'] = 1;
                        $returnArray['message'] = __('Success');
                        $returnArray['id'] = $customer->getId();
                        $returnArray['group_id'] = $customer->getGroupId();
                        $returnArray['default_billing'] = $customer->getDefaultBilling();
                        $returnArray['default_shipping'] = $customer->getDefaultShipping();
                        $returnArray['confirmation'] = $customer->getConfirmation();
                        $returnArray['created_at'] = $customer->getCreatedAt();
                        $returnArray['updated_at'] = $customer->getUpdatedAt();
                        $returnArray['created_in'] = $customer->getCreatedIn();
                        $returnArray['dob'] = $customer->getDob();
                        $returnArray['email'] = $customer->getEmail();
                        $returnArray['firstname'] = $customer->getFirstname();
                        $returnArray['lastname'] = $customer->getLastname();
                        $returnArray['middlename'] = $customer->getMiddlename();
                        $returnArray['prefix'] = $customer->getPrefix();
                        $returnArray['suffix'] = $customer->getSuffix();
                        $returnArray['gender'] = $customer->getGender();
                        $returnArray['store_id'] = $customer->getStoreId();
                        $returnArray['taxvat'] = $customer->getTaxvat();
                        $returnArray['website_id'] = $customer->getWebsiteId();
                        $returnArray['disable_auto_group_change'] = $customer->getDisableAutoGroupChange();
                        $returnArray['is_seller'] = 1;
                        $returnArray['profileurl'] = $params['profileurl'];
                        return $this->getJsonResponse(
                            $returnArray
                        );
                    } else {
                        $returnArray['success'] = 0;
                        $returnArray['message'] = __('Sorry! But this shop name/email is already present');
                        return $this->getJsonResponse(
                            $returnArray
                        );
                    }
                } else {
                    $returnArray['success'] = 0;
                    $returnArray['message'] = __('Invalid Request');
                    return $this->getJsonResponse(
                        $returnArray
                    );
                }
            } else {
                if (!empty($params['is_seller']) && !empty($params['profileurl']) && $params['is_seller'] == 1) {
                    $model = $this->_sellerFactory->create()->getCollection()->addFieldToFilter(
                        'shop_url',
                        $params['profileurl']
                    );
                    if ($model->getSize() == 0) {
                        try {
                            $customer = $this->customerInterface;
                            $customer->setWebsiteId($params['customer']['websiteId']);

                            
                            $customer->setEmail($email);
                            $customer->setFirstname($params['customer']['firstname']);
                            $customer->setLastname($params['customer']['lastname']);
                            $hashedPassword = $this->encryptorInterface->getHash($params['password'], true);

                            $this->_customerRepository->save($customer, $hashedPassword);

                            $customer = $this->_customerFactory->create();
                            
                            $customer->setWebsiteId($params['customer']['websiteId'])->loadByEmail($email);
                
                            $sellerCreated = $this->createSeller($params, $customer);
                            if ($sellerCreated == true && $customer->getId()) {
                                $returnArray['success'] = 1;
                                $returnArray['message'] = __('Success');
                                $returnArray['id'] = $customer->getId();
                                $returnArray['group_id'] = $customer->getGroupId();
                                $returnArray['default_billing'] = $customer->getDefaultBilling();
                                $returnArray['default_shipping'] = $customer->getDefaultShipping();
                                $returnArray['confirmation'] = $customer->getConfirmation();
                                $returnArray['created_at'] = $customer->getCreatedAt();
                                $returnArray['updated_at'] = $customer->getUpdatedAt();
                                $returnArray['created_in'] = $customer->getCreatedIn();
                                $returnArray['dob'] = $customer->getDob();
                                $returnArray['email'] = $customer->getEmail();
                                $returnArray['firstname'] = $customer->getFirstname();
                                $returnArray['lastname'] = $customer->getLastname();
                                $returnArray['middlename'] = $customer->getMiddlename();
                                $returnArray['prefix'] = $customer->getPrefix();
                                $returnArray['suffix'] = $customer->getSuffix();
                                $returnArray['gender'] = $customer->getGender();
                                $returnArray['store_id'] = $customer->getStoreId();
                                $returnArray['taxvat'] = $customer->getTaxvat();
                                $returnArray['website_id'] = $customer->getWebsiteId();
                                $returnArray['disable_auto_group_change'] = $customer->getDisableAutoGroupChange();
                                $returnArray['is_seller'] = 1;
                                $returnArray['profileurl'] = $params['profileurl'];
                                return $this->getJsonResponse(
                                    $returnArray
                                );
                            } else {
                                $returnArray['success'] = 0;
                                $returnArray['message'] = __('Sorry! But this shop name is not available, please set another shop name.');
                                return $this->getJsonResponse(
                                    $returnArray
                                );
                            }
                        } catch (\Exception $e) {
                            $returnArray['success'] = 0;
                            $returnArray['message'] = __($e->getMessage());
                            return $this->getJsonResponse(
                                $returnArray
                            );
                        }
                    } else {
                        $returnArray['success'] = 0;
                        $returnArray['message'] = __('Sorry! But this shop name is not available, please set another shop name.');
                        return $this->getJsonResponse(
                            $returnArray
                        );
                    }
                } else {
                    $returnArray['success'] = 0;
                    $returnArray['message'] = __('Invalid params');
                    return $this->getJsonResponse(
                        $returnArray
                    );
                }
            }
        } else {
            $returnArray['success'] = 0;
            $returnArray['message'] = __('Invalid params');
            return $this->getJsonResponse(
                $returnArray
            );
        }
    }

    private function createSeller($params, $customer)
    {

        $profileurlcount = $this->_sellerFactory->create()->getCollection();
        $profileurlcount->addFieldToFilter(
            ['shop_url','seller_id'],
            [$params['profileurl'],$customer->getId()]
        );
        if ($profileurlcount->getSize() == 0) {
            $status = $this->_marketplaceHelper->getIsPartnerApproval() ? 0 : 1;
            $customerid = $customer->getId();
            $model = $this->_sellerFactory->create();
            $model->setData('is_seller', $status);
            $model->setData('shop_url', $params['profileurl']);
            $model->setData('seller_id', $customerid);
            $model->setData('store_id', 0);
            $model->setCreatedAt($this->_date->gmtDate());
            $model->setUpdatedAt($this->_date->gmtDate());
            if ($status == 0) {
                $model->setAdminNotification(1);
            }
            $model->save();
            $loginUrl = $this->urlInterface->getUrl("marketplace/account/dashboard");
            $this->_customerSession->setBeforeAuthUrl($loginUrl);
            $this->_customerSession->setAfterAuthUrl($loginUrl);

            $helper = $this->_marketplaceHelper;
            if ($helper->getAutomaticUrlRewrite()) {
                $this->createSellerPublicUrls($params['profileurl']);
            }
            $adminStoremail = $helper->getAdminEmailId();
            $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
            $adminUsername = 'Admin';
            $senderInfo = [
                'name' => $customer->getFirstName().' '.$customer->getLastName(),
                'email' => $customer->getEmail(),
            ];
            $receiverInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $emailTemplateVariables['myvar1'] = $customer->getFirstName().' '.
            $customer->getLastName();
            $emailTemplateVariables['myvar2'] = $this->backendUrl->getUrl(
                'customer/index/edit',
                ['id' => $customer->getId()]
            );
            $emailTemplateVariables['myvar3'] = 'Admin';

            $this->emailHelper->sendNewSellerRequest(
                $emailTemplateVariables,
                $senderInfo,
                $receiverInfo
            );
            return true;
        } else {
            return false;
        }
    }

    private function createSellerPublicUrls($profileurl = '')
    {
        if ($profileurl) {
            $getCurrentStoreId = $this->_marketplaceHelper->getCurrentStoreId();

            /*
            * Set Seller Profile Url
            */
            $sourceProfileUrl = 'marketplace/seller/profile/shop/'.$profileurl;
            $requestProfileUrl = $profileurl;
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $profileRequestUrl = '';
            $urlCollectionData = $this->urlRewriteFactory->create()
            ->getCollection()
            ->addFieldToFilter('target_path', $sourceProfileUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlCollectionData as $value) {
                $urlId = $value->getId();
                $profileRequestUrl = $value->getRequestPath();
            }
            if ($profileRequestUrl != $requestProfileUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceProfileUrl)
                ->setRequestPath($requestProfileUrl)
                ->save();
            }

            /*
            * Set Seller Collection Url
            */
            $sourceCollectionUrl = 'marketplace/seller/collection/shop/'.$profileurl;
            $requestCollectionUrl = $profileurl.'/collection';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $collectionRequestUrl = '';
            $urlCollectionData = $this->urlRewriteFactory->create()
            ->getCollection()
            ->addFieldToFilter('target_path', $sourceCollectionUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlCollectionData as $value) {
                $urlId = $value->getId();
                $collectionRequestUrl = $value->getRequestPath();
            }
            if ($collectionRequestUrl != $requestCollectionUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceCollectionUrl)
                ->setRequestPath($requestCollectionUrl)
                ->save();
            }

            /*
            * Set Seller Feedback Url
            */
            $sourceFeedbackUrl = 'marketplace/seller/feedback/shop/'.$profileurl;
            $requestFeedbackUrl = $profileurl.'/feedback';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $feedbackRequestUrl = '';
            $urlFeedbackData = $this->urlRewriteFactory->create()
            ->getCollection()
            ->addFieldToFilter('target_path', $sourceFeedbackUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlFeedbackData as $value) {
                $urlId = $value->getId();
                $feedbackRequestUrl = $value->getRequestPath();
            }
            if ($feedbackRequestUrl != $requestFeedbackUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceFeedbackUrl)
                ->setRequestPath($requestFeedbackUrl)
                ->save();
            }

            /*
            * Set Seller Location Url
            */
            $sourceLocationUrl = 'marketplace/seller/location/shop/'.$profileurl;
            $requestLocationUrl = $profileurl.'/location';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $locationRequestUrl = '';
            $urlLocationData = $this->urlRewriteFactory->create()
            ->getCollection()
            ->addFieldToFilter('target_path', $sourceLocationUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlLocationData as $value) {
                $urlId = $value->getId();
                $locationRequestUrl = $value->getRequestPath();
            }
            if ($locationRequestUrl != $requestLocationUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceLocationUrl)
                ->setRequestPath($requestLocationUrl)
                ->save();
            }

            /**
             * Set Seller Policy Url
             */
            $sourcePolicyUrl = 'marketplace/seller/policy/shop/'.$profileurl;
            $requestPolicyUrl = $profileurl.'/policy';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $policyRequestUrl = '';
            $urlPolicyData = $this->urlRewriteFactory->create()
            ->getCollection()
            ->addFieldToFilter('target_path', $sourcePolicyUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);
            foreach ($urlPolicyData as $value) {
                $urlId = $value->getId();
                $policyRequestUrl = $value->getRequestPath();
            }
            if ($policyRequestUrl != $requestPolicyUrl) {
                $idPath = rand(1, 100000);
                $this->urlRewriteFactory->create()
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourcePolicyUrl)
                ->setRequestPath($requestPolicyUrl)
                ->save();
            }
        }
    }
}
