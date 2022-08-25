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
namespace Webkul\MpTimeDelivery\Block\Account;

use Webkul\Marketplace\Helper\Data;
use Webkul\MpTimeDelivery\Helper\Data as Helper;
use Webkul\Marketplace\Model\ProductFactory;
use Webkul\Marketplace\Model\OrdersFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\SellertransactionFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;

class Navigation extends \Webkul\Marketplace\Block\Account\Navigation
{
    /**
     * @var Data;
     */
    protected $mphelper;

    /**
     * @var Helper;
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductFactory $productFactory
     * @param OrdersFactory $ordersFactory
     * @param CollectionFactory $productCollection
     * @param SellertransactionFactory $sellertransaction
     * @param \Magento\Catalog\Model\ProductFactory $productModel
     * @param \Magento\Sales\Model\OrderFactory $orderModel
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistModel
     * @param \Magento\Shipping\Model\Config $shipconfig
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param MpHelper $mpHelper
     * @param Helper $helper
     * @param Data $mphelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        ProductFactory $productFactory,
        OrdersFactory $ordersFactory,
        CollectionFactory $productCollection,
        SellertransactionFactory $sellertransaction,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Magento\Sales\Model\OrderFactory $orderModel,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistModel,
        \Magento\Shipping\Model\Config $shipconfig,
        \Magento\Payment\Model\Config $paymentConfig,
        MpHelper $mpHelper,
        Helper $helper,
        Data $mphelper,
        array $data = []
    ) {
        $this->mphelper = $mphelper;
        $this->helper = $helper;
        parent::__construct(
            $context,
            $date,
            $customerSession,
            $productFactory,
            $ordersFactory,
            $productCollection,
            $sellertransaction,
            $productModel,
            $orderModel,
            $saleslistModel,
            $shipconfig,
            $paymentConfig,
            $mpHelper
        );
    }

    /**
     * Get Marketplace Helper Object
     *
     * @return object
     */
    public function getMpHelperObject()
    {
        return $this->mpHelper;
    }

    /**
     * Get Marketplace Helper Object
     *
     * @return object
     */
    public function getHelperObject()
    {
        return $this->helper;
    }
}
