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
namespace Webkul\MpTimeDelivery\Model\Config\Source;

class Minutes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Minutes getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $minutes = [];
        for ($i=0; $i < 60; $i++) {
            if ($i < 10) {
                $minutes[] = ['value' => '0'.$i, 'label' => __('0'.$i)];
            } else {
                $minutes[] =  ['value' => $i, 'label' => __($i)];
            }
        }

        return $minutes;
    }
}
