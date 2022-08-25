<?php

namespace Mpx\Checkout\Block\Cart;

/**
 *  Visible Country in page cart
 */
class LayoutProcessor
{
    /**
     * Add field set visible false to layout
     *
     * @param \Magento\Checkout\Block\Cart\LayoutProcessor $subject
     * @param \Magento\Checkout\Block\Cart\LayoutProcessor $result
     * @return $result
     */
    public function afterProcess(\Magento\Checkout\Block\Cart\LayoutProcessor $subject, $result)
    {
        if (isset($result['components']['block-summary']['children']['block-shipping']['children']
            ['address-fieldsets']['children'])
        ) {
            $fieldSetPointer = &$result['components']['block-summary']['children']['block-shipping']
            ['children']['address-fieldsets']['children'];
            $fieldSetPointer['country_id']['visible'] = false;
        }
        return $result;
    }
}
