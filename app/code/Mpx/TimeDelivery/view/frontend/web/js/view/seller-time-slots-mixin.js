/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_TimeDelivery
 * @author    Mpx
 */
define([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, _, customerData) {
    'use strict';

    return function (sellerTimeSlots) {
        return sellerTimeSlots.extend({
            /**
             * Initialize component
             *
             * @returns {initialize}
             */
            initialize: function () {
                this._super();

                customerData.get("changeevent-slots").subscribe(function (slots) {
                    $.each(slots, function (index, value) {
                        if($('#wk_days_slot_' + value['id']).val() == 'None') {
                            customerData.get("changeevent-slots")()[index]['slot_time'] =  $.mage.__('None');
                            customerData.get("changeevent-slots")()[index]['date'] =  $.mage.__('None');
                            customerData.get("changeevent-slots")()[index]['slot_id'] =  '';
                        }
                    }.bind(this));
                }.bind(this));

                return this;
            },
            /**
             * Select time slots
             *
             * @param seller
             * @param model
             * @param data
             * @param event
             * @param elem
             * @param eventData
             * @returns {*}
             */
            selectTimeSlot: function (seller, model, data, event, elem, eventData) {
                if (elem && !elem.length) {
                    var isSelectedSlot = false;

                    $.each(this.selectedSlots(), function (index, value) {
                        if (seller.id == value.id) {
                            isSelectedSlot = true;
                        }
                    });

                    if (!isSelectedSlot) {
                        this.selectedSlots.push({
                            'id': seller.id,
                            'name': seller.name,
                            'slot_time': $.mage.__('None'),
                            'date': $.mage.__('None'),
                            'slot_id': ''
                        });
                        this.sellerCount(this.sellerCount() + 1);
                        customerData.set("changeevent-slots", this.selectedSlots());
                        customerData.set("selected-slots", this.selectedSlots());
                    }

                    return true;
                }

                this._super(seller, model, data, event, elem, eventData);

                return true;
            }
        });
    };
});