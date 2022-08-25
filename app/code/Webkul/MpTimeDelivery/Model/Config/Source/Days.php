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

class Days implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Days getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $days = [
            ['value' => 'Sunday',       'label' => __('Sunday')],
            ['value' => 'Monday',       'label' => __('Monday')],
            ['value' => 'Tuesday',      'label' => __('Tuesday')],
            ['value' => 'Wednesday',    'label' => __('Wednesday')],
            ['value' => 'Thursday',     'label' => __('Thursday')],
            ['value' => 'Friday',       'label' => __('Friday')],
            ['value' => 'Saturday',     'label' => __('Saturday')],
        ];

        return $days;
    }
}
