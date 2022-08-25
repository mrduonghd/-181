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
namespace Webkul\MpTimeDelivery\Block\Adminhtml\Order;

use Webkul\MpTimeDelivery\Helper\Data as Helper;

class View extends \Magento\Sales\Block\Adminhtml\Order\View
{
    /**
     * @var Helper;
     */
    protected $helper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Sales\Helper\Reorder $reorderHelper
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        Helper $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $salesConfig, $reorderHelper, $data);
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
