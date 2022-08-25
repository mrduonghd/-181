<?php
/**
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Model\Config\Source;

class Yesno implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
          [
              'value' => 'yes',
              'label' => __('Yes')
          ],[
              'value' => 'no',
              'label' => __('No')
          ]
        ];
    }
}
