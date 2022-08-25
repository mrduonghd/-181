<?php
/**
 * Webkul Mpshipping Distanceset Edit Block
 *
 * @category    Webkul
 * @package     Webkul_Mpshipping
 * @author      Webkul Software Private Limited
 *
 */

namespace Webkul\Mpshipping\Block\Adminhtml\Distanceset;

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
        $this->_objectId = 'entity_id';
        $this->_controller = 'adminhtml_distanceset';
        $this->_blockGroup = 'Webkul_Mpshipping';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Shipping'));
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('mpshippingDist_shipping')->getId()) {
            return __("Edit Rule");
        } else {
            return __('New Rule');
        }
    }
}
