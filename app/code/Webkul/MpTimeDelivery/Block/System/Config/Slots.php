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
namespace Webkul\MpTimeDelivery\Block\System\Config;
 
use Magento\Config\Block\System\Config\Form\Field;
use Webkul\MpTimeDelivery\Block\Adminhtml\Options;
 
class Slots extends Field
{
    const SLOTS_TEMPLATE = 'system/config/slots.phtml';
 
     /**
      * Set template to itself
      *
      * @return $this
      */
    protected function _prepareLayout()
    {
        $this->addChild('time_delivery_box', Options::class);

        if (!$this->getTemplate()) {
            $this->setTemplate(static::SLOTS_TEMPLATE);
        }
        return parent::_prepareLayout();
    }

    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

     /**
      * Get the button and scripts contents
      *
      * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
      * @return string
      */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Get OptionsBox Html
     *
     * @return string
     */
    public function getOptionsBoxHtml()
    {
        return $this->getChildHtml('time_delivery_box');
    }

    /**
     * Retrieve Form Action Url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getChildBlock('time_delivery_config')->getPostActionUrl();
    }
}
