/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_TimeSlotDelivery
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define(
    [
    'jquery',
    'Webkul_MpTimeDelivery/js/view/seller-time-slots',
    'Magento_Customer/js/customer-data'
    ],
    function ($, sellerTimeSlots, customerData) {
        'use strict';
        
        return function (Payment) {
            return Payment.extend(
                {
                    getData: function () {
                        var data = this._super();
                        if (sellerTimeSlots().isEnabled && (this.getSlotInfo()).length != null) {
                            data['extension_attributes'] = {
                                seller_data: this.getSlotInfo(),
                            }
                        }
                        return data;
                    },
                    getSlotInfo: function () {
                        return JSON.stringify(customerData.get('changeevent-slots')());
                    }
                }
            );
        }
    }
);