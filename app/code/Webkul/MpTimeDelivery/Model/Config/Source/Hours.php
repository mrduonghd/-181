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

class Hours implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Hours getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $hours = [];
        for ($i=0; $i < 24; $i++) {
            if ($i < 10) {
                $hours[] = ['value' => '0'.$i, 'label' => __('0'.$i)];
            } else {
                $hours[] =  ['value' => $i, 'label' => __($i)];
            }
        }

        return $hours;
    }
}
