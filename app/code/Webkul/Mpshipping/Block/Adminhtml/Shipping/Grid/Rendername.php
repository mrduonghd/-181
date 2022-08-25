<?php

/**
 * Webkul Mpshipping Shipping Edit Tab Shipping Admin render name block
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Block\Adminhtml\Shipping\Grid;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class Rendername extends AbstractRenderer
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    protected $_actions = [];

    protected $_index = 0;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Escaper $_escaper,
        array $data = []
    ) {
        $this->_escaper = $_escaper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->_actions = [];
        $rowData = $row->getData();
        if ($rowData['customer_name']=='') {
            $actions[] = __('Admin');
        } else {
            $actions[] = $rowData['customer_name'];
        }
        $this->addToActions($actions);
        return $this->_actionsToHtml();
    }

    /**
     * Render options array as a HTML string
     *
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions = [])
    {
        $html = [];
        $attributesObject = new \Magento\Framework\DataObject();

        if (empty($actions)) {
            $actions = $this->_actions;
        }
        foreach ($actions[0] as $action) {
            $html[] = '<span>' . $action . '</span>';
        }
        return implode('', $html);
    }

    /**
     * Add one action array to all options data storage
     *
     * @param array $actionArray
     * @return void
     */
    public function addToActions($actionArray)
    {
        $this->_actions[] = $actionArray;
    }
}
