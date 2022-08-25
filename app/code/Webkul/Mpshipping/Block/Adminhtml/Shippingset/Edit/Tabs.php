<?php

/**
 * Webkul Mpshipping Shippingset Edit Tabs
 *
 * @category    Webkul
 * @package     Webkul_Mpshipping
 * @author      Webkul Software Private Limited
 *
 */

namespace Webkul\Mpshipping\Block\Adminhtml\Shippingset\Edit;

/**
 * Warehouse page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Shipping Set Information'));
    }

    /**
     * @return $this
     */
    public function _beforeToHtml()
    {
        $this->addTab(
            'main_section',
            [
                'label' => __('Shipping Set Info'),
                'title' => __('Shipping Set Info'),
                'content' => $this->getLayout()->createBlock(
                    \Webkul\Mpshipping\Block\Adminhtml\Shippingset\Edit\Tab\Main::class
                )->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
