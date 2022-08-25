<?php

/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Model;

class Shippingtype
{
    public function toOptionArray()
    {
        $data = [
                    [
                        'value' => 'fixed',
                        'label' => 'Fixed',
                    ],
                    [
                        'value' => 'free',
                        'label' => 'Free',
                    ],
            ];

        return  $data;
    }
}
