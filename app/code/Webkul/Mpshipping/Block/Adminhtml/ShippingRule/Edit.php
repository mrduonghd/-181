<?php
/**
 * Webkul Mpshipping Shippingset Edit Controller
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Block\Adminhtml\ShippingRule;

/**
 * User edit page
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {

        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_objectId = 'mpshipping_id';
        $this->_controller = 'adminhtml_shippingRule';
        $this->_blockGroup = 'Webkul_Mpshipping';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Shipping'));
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('mpshippingrule_shipping')->getId()) {
            return __("Edit Shipping Rule");
        } else {
            return __('New Shipping Rule');
        }
    }

    /**
     * Get form save URL
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('mpshipping/shipping/update');
    }
}
