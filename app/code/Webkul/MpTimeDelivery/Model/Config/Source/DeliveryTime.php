<?php

namespace Webkul\MpTimeDelivery\Model\Config\Source;


class DeliveryTime extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $renderer = $this->getLayout()->createBlock(
            'Webkul\MpTimeDelivery\Block\TimeSlider'
        );
        $renderer->setElement($element);

        return $renderer->toHtml();
    }
}