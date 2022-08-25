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

class Items extends \Magento\Sales\Block\Order\Items
{
    /**
     * @var Helper;
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollectionFactory
     * @param JsonHelper $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollectionFactory,
        Helper $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $data, $itemCollectionFactory);
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
