/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDelivery
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Webkul_MpTimeDelivery/js/view/seller-time-slots',
        'Magento_Customer/js/customer-data'
    ],
    function ($, Component, quote, sellerTimeSlots, customerData) {
        'use strict';
        return Component.extend(
            {
                defaults: {
                    template: 'Webkul_MpTimeDelivery/timeslot-information'
                },
                initialize: function () {
                    this._super();
                    this.isEnabled = window.checkoutConfig.isEnabled;
                },
                getSlotInfo: function () {
                    var changeEventSlots = customerData.get("changeevent-slots")();
                    if ($.isEmptyObject(changeEventSlots)){
                        if ($.isEmptyObject(sellerTimeSlots().selectedSlots())) {
                            var slots = customerData.get("selected-slots");
                            return slots;
                        }
                        return sellerTimeSlots().selectedSlots();
                       
                    }else{
                        return customerData.get("changeevent-slots");
                    }
                    
                },
            }
        );
    }
);
