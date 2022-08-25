<?php
/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */
namespace Mpx\Marketplace\Block\Order;

use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollection;
use Magento\Store\Model\ScopeInterface;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory as ShipmentItemCollection;
use Webkul\Marketplace\Model\OrdersFactory as MpOrderModel;
use Webkul\Marketplace\Model\SaleslistFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory as TrackingInvoiceNumber;

class Items extends \Webkul\Marketplace\Block\Order\Items
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var AddressRenderer
     */
    protected $addressRenderer;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_links = [];

    /**
     * @var Purchased
     */
    protected $_purchasedLinks;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $_purchasedFactory;

    /**
     * @var CollectionFactory
     */
    protected $_itemsFactory;

    /**
     * @var \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
     */
    protected $defaultRenderer;

    /**
     * @var MpOrderModel
     */
    protected $mpOrderModel;

    /**
     * @var Creditmemo
     */
    protected $creditmemoModel;

    /**
     * @var Magento\Sales\Model\Order\Creditmemo\ItemFactory
     */
    protected $creditmemoItem;

    /**
     * @var InvoiceFactory
     */
    protected $invoiceModel;

    /**
     * @var SaleslistFactory
     */
    protected $saleslistModel;

    /**
     * @var \Webkul\Marketplace\Helper\Orders
     */
    protected $ordersHelper;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    protected $productRepository;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var OrderItemCollection
     */
    protected $itemCollectionFactory;
    /**
     * @var ShipmentItemCollection
     */
    protected $shipmentItemCollectionFactory;

    /**
     * @var TrackingInvoiceNumber
     */
    protected $trackingInvoiceNumber;
    /**
     * @param Order                                             $order
     * @param Customer                                          $customer
     * @param \Magento\Customer\Model\Session                   $customerSession
     * @param \Magento\Framework\Registry                       $coreRegistry
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param AddressRenderer                                   $addressRenderer
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory
     * @param CollectionFactory                                 $itemsFactory
     * @param MpOrderModel                                      $mpOrderModel
     * @param Creditmemo                                        $creditmemoModel
     * @param \Magento\Sales\Model\Order\Creditmemo\ItemFactory $creditmemoItem
     * @param InvoiceFactory                                    $invoiceModel
     * @param SaleslistFactory                                  $saleslistModel
     * @param \Webkul\Marketplace\Helper\Orders                 $ordersHelper
     * @param ProductRepositoryInterfaceFactory                 $productRepository
     * @param \Magento\Shipping\Model\Config                    $shippingConfig
     * @param \Magento\Shipping\Model\CarrierFactory            $carrierFactory
     * @param OrderItemCollection                               $itemCollectionFactory
     * @param ShipmentItemCollection                            $shipmentItemCollectionFactory
     * @param TrackingInvoiceNumber                             $trackingInvoiceNumber
     * @param array                                             $data
     */

    public function __construct(
        Order $order,
        Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Element\Template\Context $context,
        AddressRenderer $addressRenderer,
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory,
        \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $defaultRenderer,
        CollectionFactory $itemsFactory,
        MpOrderModel $mpOrderModel,
        Creditmemo $creditmemoModel,
        \Magento\Sales\Model\Order\Creditmemo\ItemFactory $creditmemoItem,
        InvoiceFactory $invoiceModel,
        SaleslistFactory $saleslistModel,
        \Webkul\Marketplace\Helper\Orders $ordersHelper,
        ProductRepositoryInterfaceFactory $productRepository,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        OrderItemCollection $itemCollectionFactory,
        ShipmentItemCollection $shipmentItemCollectionFactory,
        TrackingInvoiceNumber $trackingInvoiceNumber,
        array $data = []
    ) {

        $this->ShipmentItemCollection = $shipmentItemCollectionFactory;
        $this->trackingInvoiceNumber = $trackingInvoiceNumber;
        parent::__construct($order,$customer,$customerSession, $coreRegistry,$context,$addressRenderer,$purchasedFactory,
            $defaultRenderer,$itemsFactory,$mpOrderModel,$creditmemoModel,$creditmemoItem,$invoiceModel,$saleslistModel,$ordersHelper,
            $productRepository,$shippingConfig,$carrierFactory,$itemCollectionFactory,$data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->itemsPerPage = $this->_scopeConfig->getValue('sales/orders/items_per_page');

        $this->itemCollection = $this->ShipmentItemCollection->create();
        $salesShipmentItem = $this->itemCollection->getTable('sales_shipment');
        $marketplaceSaleslist = $this->itemCollection->getTable('marketplace_saleslist');

        $this->itemCollection->getSelect()->join(
            $salesShipmentItem.' as sales_shipment',
            'sales_shipment.entity_id = main_table.parent_id'
        );
        $this->itemCollection->getSelect()->join(
            $marketplaceSaleslist.' as msl',
            ' msl.order_item_id = main_table.order_item_id AND msl.order_id = sales_shipment.order_id',
            [
                'msl.seller_id AS seller_id',
                'msl.total_amount AS total_amount',
                'msl.actual_seller_amount AS actual_seller_amount',
                'msl.total_commission AS total_commission',
                'msl.magepro_price AS magepro_price',
                'msl.applied_coupon_amount AS applied_coupon_amount',
                'msl.total_tax AS total_tax'
            ]
        )->where('msl.seller_id = "'.$this->getCustomerId().'" AND sales_shipment.order_id = '.$this->getOrder()->getId());
        $this->itemCollection->getSelect()->group("sales_shipment.entity_id");
        $this->itemCollection->setOrder("sales_shipment.entity_id","ASC");
        $this->itemCollection = $this->addAdditionalFilters($this->itemCollection);
        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('marketplace_order_item_pager');
        if ($pagerBlock) {
            $pagerBlock->setLimit($this->itemsPerPage);
            //here pager updates collection parameters
            $pagerBlock->setCollection($this->itemCollection);
            $pagerBlock->setAvailableLimit([$this->itemsPerPage]);
            $pagerBlock->setShowAmounts($this->isPagerDisplayed());
        }
        return $this;
    }

    /**
     * Get tracking invoice number
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection
     */
    public function getTrackingInvoiceNumber()
    {
        return $this->trackingInvoiceNumber->create()
            ->addFieldToFilter('order_id', ['eq' => $this->getOrder()->getId()]);
    }
}
