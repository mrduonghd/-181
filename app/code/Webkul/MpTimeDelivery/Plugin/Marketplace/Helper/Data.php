<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDelivery
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTimeDelivery\Plugin\Marketplace\Helper;

class Data
{
    /**
     * function to run to change the return data of afterIsSeller.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array                           $result
     *
     * @return bool
     */
    public function afterGetControllerMappedPermissions(
        \Webkul\Marketplace\Helper\Data $helperData,
        $result
    ) {
        $result['timedelivery/account/index'] = 'timedelivery/account/save';
        return $result;
    }
}
