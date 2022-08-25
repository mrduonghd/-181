define(
    [
    'jquery',
    'Webkul_MpTimeDelivery/js/view/seller-time-slots',
    'mage/translate'
    ],
    function (
        $,
        sellerTimeSlots,
        $t
    ) {
        'use strict';
        return function (Shipping) {
            return Shipping.extend(
                {

                    validateShippingInformation: function () {
                        var sellerCount = sellerTimeSlots().sellerCount();
                        var totalSellerCount = sellerTimeSlots().totalSellerCount();
                        if (sellerCount != totalSellerCount) {
                            this.errorValidationMessage('Please select delivery slots for all sellers.');
                            return false;
                        }
                        return this._super();
                    }
                }
            );
        }
    }
);