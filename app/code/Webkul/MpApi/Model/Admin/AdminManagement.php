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
namespace Webkul\MpApi\Model\Admin;

use Magento\Framework\Controller\ResultFactory;

class AdminManagement implements \Webkul\MpApi\Api\AdminManagementInterface
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

    /** @var \Magento\Sales\Model\OrderRepository */
    public $_orderRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadata;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $_eventManager;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductFactory;

    /**
     * @var \Webkul\Marketplace\Model\SaleslistFactory
     */
    protected $saleslistFactory;

    /**
     * @var \Webkul\Marketplace\Model\OrdersFactory
     */
    protected $mpOrdersFactory;

    /**
     * @var \Webkul\Marketplace\Model\Saleperpartner
     */
    protected $salesPerPartnerFactory;

    /**
     * @var \Webkul\Marketplace\Model\SellertransactionFactory
     */
    protected $sellerTransactionsFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var \Webkul\MpApi\Api\Data\ResponseInterface
     */
    protected $responseApi;
    
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
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Directory\Model\Country $country,
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
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $sellerProduct,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Event\Manager $eventManager,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrdersFactory,
        \Webkul\Marketplace\Model\SaleperpartnerFactory $salesPerPartnerFactory,
        \Webkul\Marketplace\Model\SellertransactionFactory $sellerTransactionsFactory,
        \Webkul\Marketplace\Helper\Email $emailHelper,
        \Webkul\MpApi\Api\Data\ResponseInterface $responseApi
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
        $this->date = $date;
        $this->_sellerProduct = $sellerProduct;
        $this->_productMetadata = $productMetadata;
        $this->_eventManager = $eventManager;
        $this->mpProductFactory = $mpProductFactory;
        $this->saleslistFactory = $saleslistFactory;
        $this->mpOrdersFactory = $mpOrdersFactory;
        $this->salesPerPartnerFactory = $salesPerPartnerFactory;
        $this->sellerTransactionsFactory = $sellerTransactionsFactory;
        $this->emailHelper = $emailHelper;
        $this->responseApi = $responseApi;
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

    protected function _isOwner($customerId)
    {
        if ($this->getApiUser()->getUserId() !== $customerId) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        return true;
    }

    /**
     * depricated
     *
     * Interface to get all sellers.
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
            $returnArray['error'] = __("invalid request");
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
            $returnArray['error'] = __('invalid request');
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
            $collection = $this->mpOrdersFactory->create()
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    ['in' => $collectionOrders->getData()]
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
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function payToSeller($id)
    {
        try {
            $returnArray = [];
            $wholedata = $this->_request->getPost();
            $actparterprocost = 0;
            $totalamount = 0;
            $sellerId = $id;
            $helper = $this->_marketplaceHelper;
            $taxToSeller = $helper->getConfigTaxManage();
            $orderinfo = '';
            $collection = $this->saleslistFactory->create()->getCollection()
            ->addFieldToFilter('entity_id', $wholedata['entity_id'])
            ->addFieldToFilter('order_id', ['neq' => 0])
            ->addFieldToFilter('paid_status', 0)
            ->addFieldToFilter('cpprostatus', ['neq' => 0]);
            if ($collection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('cannot pay to seller')
                );
            }
            foreach ($collection as $row) {
                $sellerId = $row->getSellerId();
                $order = $this->_orderRepository->get($row['order_id']);
                $taxAmount = $row['total_tax'];
                $marketplaceOrders = $this->mpOrdersFactory->create()->getCollection()
                ->addFieldToFilter('order_id', $row['order_id'])
                ->addFieldToFilter('seller_id', $sellerId);
                foreach ($marketplaceOrders as $tracking) {
                    $taxToSeller = $tracking['tax_to_seller'];
                }
                $vendorTaxAmount = 0;
                if ($taxToSeller) {
                    $vendorTaxAmount = $taxAmount;
                }
                $codCharges = 0;
                $shippingCharges = 0;
                if (!empty($row['cod_charges'])) {
                    $codCharges = $row->getCodCharges();
                }
                if ($row->getIsShipping() == 1) {
                    foreach ($marketplaceOrders as $tracking) {
                        $shippingamount = $tracking->getShippingCharges();
                        $refundedShippingAmount = $tracking->getRefundedShippingCharges();
                        $shippingCharges = $shippingamount - $refundedShippingAmount;
                    }
                }
                $actparterprocost = $actparterprocost +
                    $row->getActualSellerAmount() +
                    $vendorTaxAmount +
                    $codCharges +
                    $shippingCharges;
                $totalamount = $totalamount +
                    $row->getTotalAmount() +
                    $taxAmount +
                    $codCharges +
                    $shippingCharges;
                $orderinfo = $orderinfo."<tr>
                    <td class='item-info'>".$row['magerealorder_id']."</td>
                    <td class='item-info'>".$row['magepro_name']."</td>
                    <td class='item-qty'>".$row['magequantity']."</td>
                    <td class='item-price'>".$order->formatPrice($row['magepro_price'])."</td>
                    <td class='item-price'>".$order->formatPrice($row['total_commission'])."</td>
                    <td class='item-price'>".$order->formatPrice($row['actual_seller_amount']).'</td>
                </tr>';
            }
            if ($actparterprocost) {
                $collectionverifyread = $this->salesPerPartnerFactory->create()->getCollection()
                ->addFieldToFilter('seller_id', $sellerId);
                if (count($collectionverifyread) >= 1) {
                    $id = 0;
                    $totalremain = 0;
                    $amountpaid = 0;
                    foreach ($collectionverifyread as $verifyrow) {
                        $id = $verifyrow->getId();
                        if ($verifyrow->getAmountRemain() >= $actparterprocost) {
                            $totalremain = $verifyrow->getAmountRemain() - $actparterprocost;
                        }
                        $amountpaid = $verifyrow->getAmountReceived();
                    }
                    $verifyrow = $this->salesPerPartnerFactory->create()->load($id);
                    $totalrecived = $actparterprocost + $amountpaid;
                    $verifyrow->setLastAmountPaid($actparterprocost);
                    $verifyrow->setAmountReceived($totalrecived);
                    $verifyrow->setAmountRemain($totalremain);
                    $verifyrow->setUpdatedAt($this->date->gmtDate());
                    $verifyrow->save();
                } else {
                    $percent = $helper->getConfigCommissionRate();
                    $collectionf = $this->salesPerPartner->create();
                    $collectionf->setSellerId($sellerId);
                    $collectionf->setTotalSale($totalamount);
                    $collectionf->setLastAmountPaid($actparterprocost);
                    $collectionf->setAmountReceived($actparterprocost);
                    $collectionf->setAmountRemain(0);
                    $collectionf->setCommissionRate($percent);
                    $collectionf->setTotalCommission($totalamount - $actparterprocost);
                    $collectionf->setCreatedAt($this->date->gmtDate());
                    $collectionf->setUpdatedAt($this->date->gmtDate());
                    $collectionf->save();
                }

                $uniqueId = $this->checktransid();
                $transid = '';
                $transactionNumber = '';
                if ($uniqueId != '') {
                    $sellerTrans = $this->sellerTransactionsFactory->create()->getCollection()
                    ->addFieldToFilter('transaction_id', $uniqueId);
                    if (count($sellerTrans)) {
                        $id = 0;
                        foreach ($sellerTrans as $value) {
                            $id = $value->getId();
                        }
                        if ($id) {
                            $this->sellerTransactionsFactory->create()->load($id)->delete();
                        }
                    }
                    $sellerTrans = $this->sellerTransactionsFactory->create();
                    $sellerTrans->setTransactionId($uniqueId);
                    $sellerTrans->setTransactionAmount($actparterprocost);
                    $sellerTrans->setType('Manual');
                    $sellerTrans->setMethod('Manual');
                    $sellerTrans->setSellerId($sellerId);
                    $sellerTrans->setCustomNote($wholedata['seller_pay_reason']);
                    $sellerTrans->setCreatedAt($this->date->gmtDate());
                    $sellerTrans->setUpdatedAt($this->date->gmtDate());
                    $sellerTrans = $sellerTrans->save();
                    $transid = $sellerTrans->getId();
                    $transactionNumber = $sellerTrans->getTransactionId();
                }

                $collection = $this->saleslistFactory->create()->load($wholedata['entity_id']);

                $cpprostatus = $collection->getCpprostatus();
                $paidStatus = $collection->getPaidStatus();
                $orderId = $collection->getOrderId();

                if ($cpprostatus == 1 && $paidStatus == 0 && $orderId != 0) {
                    $collection->setPaidStatus(1);
                    $collection->setTransId($transid)->save();
                    $data['id'] = $collection->getOrderId();
                    $data['seller_id'] = $collection->getSellerId();
                    $this->_eventManager->dispatch(
                        'mp_pay_seller',
                        [$data]
                    );
                }

                $seller = $this->_customerFactory->create()->load($sellerId);

                $emailTempVariables = [];

                $adminStoreEmail = $helper->getAdminEmailId();
                $adminEmail = $adminStoreEmail ? $adminStoreEmail : $helper->getDefaultTransEmailId();
                $adminUsername = 'Admin';

                $senderInfo = [];
                $receiverInfo = [];

                $receiverInfo = [
                    'name' => $seller->getName(),
                    'email' => $seller->getEmail(),
                ];
                $senderInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];

                $emailTempVariables['myvar1'] = $seller->getName();
                $emailTempVariables['myvar2'] = $transactionNumber;
                $emailTempVariables['myvar3'] = $this->date->gmtDate();
                $emailTempVariables['myvar4'] = $actparterprocost;
                $emailTempVariables['myvar5'] = $orderinfo;
                $emailTempVariables['myvar6'] = $wholedata['seller_pay_reason'];

                $this->emailHelper->sendSellerPaymentEmail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );

                $returnArray['message'] = __('Payment has been successfully done for this seller');
                $returnArray['status'] = self::SUCCESS;
                return $this->getJsonResponse($returnArray);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = __($e->getMessage());
            $returnArray['status'] = self::LOCAL_ERROR;
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['message'] = __('invalid request');
            $returnArray['status'] = self::SEVERE_ERROR;
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function assignProduct($sellerId)
    {
        try {
            $returnArray = [];
            $wholedata = $this->_request->getPost();
            reset($wholedata);
            $first_key = key($wholedata);
            if (!isset($wholedata['productIds'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('%1 is invalid key', $first_key)
                );
            }
            if ($wholedata['productIds']=="") {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('please set id(s) of product')
                );
            }
            $collection = $this->_sellerlistCollectionFactory
            ->create()
            ->addFieldToSelect(
                '*'
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            )
            ->addFieldToFilter(
                'is_seller',
                ['eq' => 1]
            );
            if ($collection->getSize() <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('cannot found seller')
                );
            }
            // set product status to 1 to assign selected products from seller
            $productCollection = $this->_productFactory->create()->getCollection()
            ->addFieldToFilter(
                'entity_id',
                ['in' => $wholedata['productIds']]
            );
            if ($productCollection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('cannot found any product(s)')
                );
            }
            $prdAssignTosellerCount = 0;
            foreach ($productCollection as $product) {
                $proid = $product->getID();
                $userid = '';
                $collection = $this->mpProductFactory->create()->getCollection()
                ->addFieldToFilter(
                    'mageproduct_id',
                    $proid
                );
                $flag = 1;
                foreach ($collection as $coll) {
                    $flag = 0;
                    if ($sellerId != $coll['seller_id']) {
                        $returnArray['message'][] = __('The product with id %1 is already assigned to other seller.', $proid);
                    } else {
                        $returnArray['message'][] = __('The product with id %1 is already assigned to the seller.', $proid);
                        $coll->setAdminassign(1)->save();
                    }
                }
                if ($flag) {
                    $prdAssignTosellerCount++;
                    $collection1 = $this->mpProductFactory->create();
                    $collection1->setMageproductId($proid);
                    $collection1->setSellerId($sellerId);
                    $collection1->setStatus($product->getStatus());
                    $collection1->setAdminassign(1);
                    $collection1->setCreatedAt($this->date->gmtDate());
                    $collection1->setUpdatedAt($this->date->gmtDate());
                    $collection1->save();
                }
            }
            $returnArray['message'][] = __('%1 Product(s) has been successfully assigned to seller', $prdAssignTosellerCount);
            $returnArray['status'] = self::SUCCESS;
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'][] = __($e->getMessage());
            $returnArray['status'] = self::LOCAL_ERROR;
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['message'][] = __('invalid request');
            $returnArray['status'] = self::SEVERE_ERROR;
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * @inheritDoc
     */
    public function unassignProduct($sellerId)
    {
        try {
            $returnArray = [];
            $wholedata = $this->_request->getPost();
            reset($wholedata);
            $first_key = key($wholedata);
            if (!isset($wholedata['productIds'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('%1 is invalid key', $first_key)
                );
            }
            if ($wholedata['productIds']=="") {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('please set id(s) of product')
                );
            }
            $collection = $this->_sellerlistCollectionFactory
            ->create()
            ->addFieldToSelect(
                '*'
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            )
            ->addFieldToFilter(
                'is_seller',
                ['eq' => 1]
            );
            if ($collection->getSize() <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('cannot found seller with id %1', $sellerId)
                );
            }
            $productCollection = $this->mpProductFactory->create()->getCollection()
            ->addFieldToFilter(
                'entity_id',
                ['in' => $wholedata['productIds']]
            );
            if ($productCollection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('cannot found any product(s)')
                );
            }
            $prdUnassignTosellerCount = 0;
            foreach ($productCollection as $product) {
                $proid = $product->getID();
                $userid = '';
                $collection = $this->mpProductFactory->create()->getCollection()
                ->addFieldToFilter(
                    'mageproduct_id',
                    $proid
                );
                $flag = 1;
                foreach ($collection as $coll) {
                    $flag = 0;
                    if ($sellerId != $coll['seller_id']) {
                        $returnArray['message'][] = __('The product with id %1 is already assigned to other seller.', $proid);
                    } else {
                        $coll->delete();
                        $prdUnassignTosellerCount++;
                    }
                }
                if ($flag) {
                    $returnArray['message'][] = __('The product with id %1 is not assigned to the seller.', $proid);
                }
            }
            $returnArray['message'][] = __('%1 Product(s) has been successfully unassigned from seller', $prdUnassignTosellerCount);
            $returnArray['status'] = self::SUCCESS;
            return $this->getJsonResponse($returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'][] = __($e->getMessage());
            $returnArray['status'] = self::LOCAL_ERROR;
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['message'][] = __('invalid request');
            $returnArray['status'] = self::SEVERE_ERROR;
            return $this->getJsonResponse($returnArray);
        }
    }

    /**
     * getJsonResponse returns json response.
     *
     * @param array $responseContent
     *
     * @return JSON
     */
    protected function getJsonResponse($responseContent = [])
    {
        $res = $this->responseApi;
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

    public function randString(
        $length,
        $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
    ) {
        $str = 'tr-';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count - 1)];
        }

        return $str;
    }
    
    public function checktransid()
    {
        $uniqueId = $this->randString(11);
        $collection = $this->sellerTransactionsFactory->create()
        ->getCollection()
        ->addFieldToFilter('transaction_id', $uniqueId);
        $i = 0;
        foreach ($collection as $value) {
            ++$i;
        }
        if ($i != 0) {
            $this->checktransid();
        } else {
            return $uniqueId;
        }
    }
}
