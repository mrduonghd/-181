<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDelivery
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTimeDelivery\Block\Order;

use Webkul\MpTimeDelivery\Helper\Data as Helper;
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Webkul\Marketplace\Model\OrdersFactory as MpOrderModel;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\InvoiceFactory;
use Webkul\Marketplace\Model\SaleslistFactory;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollection;

class View extends \Webkul\Marketplace\Block\Order\View
{
    /**
     * @var Helper;
     */
    protected $helper;

   /**
    * @param Helper $helper
    * @param Order $order
    * @param Customer $customer
    * @param \Magento\Customer\Model\Session $customerSession
    * @param \Magento\Framework\Registry $coreRegistry
    * @param \Magento\Framework\View\Element\Template\Context $context
    * @param AddressRenderer $addressRenderer
    * @param \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory
    * @param \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $defaultRenderer
    * @param CollectionFactory $itemsFactory
    * @param MpOrderModel $mpOrderModel
    * @param Creditmemo $creditmemoModel
    * @param \Magento\Sales\Model\Order\Creditmemo\ItemFactory $creditmemoItem
    * @param InvoiceFactory $invoiceModel
    * @param SaleslistFactory $saleslistModel
    * @param \Webkul\Marketplace\Helper\Orders $ordersHelper
    * @param ProductRepositoryInterfaceFactory $productRepository
    * @param \Magento\Shipping\Model\Config $shippingConfig
    * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
    * @param OrderItemCollection $itemCollectionFactory
    * @param array $data
    */
    public function __construct(
        Helper $helper,
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
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $order,
            $customer,
            $customerSession,
            $coreRegistry,
            $context,
            $addressRenderer,
            $purchasedFactory,
            $defaultRenderer,
            $itemsFactory,
            $mpOrderModel,
            $creditmemoModel,
            $creditmemoItem,
            $invoiceModel,
            $saleslistModel,
            $ordersHelper,
            $productRepository,
            $shippingConfig,
            $carrierFactory,
            $itemCollectionFactory,
            $data
        );
    }

    /**
     * Get Helper Object
     *
     * @return object
     */
    public function getHelperObject()
    {
        return $this->helper;
    }
}
