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
    'ko',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    "jquery/ui"
    ],
    function ($, ko, Component, customerData) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    template: 'Webkul_MpTimeDelivery/seller-time-timedropdown'
                },
                totalSellerCount: ko.observable(0),
                sellerCount: ko.observable(0),
                selectedSlots: ko.observableArray([]),
                initialize: function () {
                    this._super();
                    var self = this;
                    this.allowedDays = window.checkoutConfig.allowed_days;
                    this.isEnabled = window.checkoutConfig.isEnabled;
                    this.sellersData = window.checkoutConfig.seller;
                    this.startDate = window.checkoutConfig.start_date;
                    this.slots = ko.observableArray([]);
                    this.sortedSlots = ko.observableArray([]),
                    this.isChecked = ko.observable(false);
                    this.currentDate = this.startDate;
                    this.maxDays = window.checkoutConfig.max_days;
                    $.each(
                        this.sellersData,
                        function (i, v) {
                            self.slots.push(v);
                        }
                    );
                },
                getSellerSlotData: function () {
                    this.totalSellerCount(this.slots().length);
                    return this.slots;
                },
                getSortedSlots: function (data) {
                    var ordered = {};
                    Object.keys(data).sort().forEach(
                        function (key) {
                            ordered[key] = data[key];
                        }
                    );
                    return ordered;
                },
                getDate: function (sellerId , cday) {
                    
                    var sellerStartDate = this.sellersData[sellerId].seller_start_date;
                    var cDate = new Date(cday);
                    var cDay = cDate.getDay();
                    var returnDate;
                    var check = 0;
                    for (var i = 0; i <= this.maxDays; i++) {

                        var nDate = new Date(sellerStartDate);    
                        
                        var day = nDate.getDate();
                        var month = nDate.getMonth() + 1;
                        if (day < 10) {
                            day = "0" + day;
                        }
                       if (month < 10) {
                            month = "0" + month;
                        }    
                        cday = cday.replace(/-/g, "/");
            
                        var d = new Date(nDate.getFullYear() + "-" + month + "-" + day);
                        var n = d.getDay();
                        let convertedDate =new Date(cday + " " + '3:00:00 AM').toLocaleString("en-US", {timeZone: window.checkoutConfig.timezone});
                        let inMiSec = Date.parse(convertedDate);
            
                        if (n == cDay) {
                            returnDate = $.datepicker.formatDate(
                                'DD, d MM, yy',
                                new Date(inMiSec)
                            );
                            break;
                        }
                        check++;
                    }
                    return returnDate;
                },
                checkDay: function (day, sellerStart) {
                    if (sellerStart) {
                        var d = new Date(sellerStart);
                    } else {
                        var d = new Date(this.startDate);
                    }
                    var requestedDay = new Date(day);
                    if (requestedDay >= d) {
                        return true;
                    }
                    return false;
                },
                checkTime: function (time, date) {
                    var result = time.split('-');
                    var currentTime = new Date().getTime();
                    var slotTime = new Date(this._convertDate(date + " " + result[1].replace(' ', ''))).getTime();

                    if (currentTime <= slotTime) {
                        return true;
                    }
                    return false;
                },
                _convertDate: function (date) {
                    /* # valid js Date and time object format (YYYY-MM-DDTHH:MM:SS) */
                    var dateTimeParts = date.split(' ');

                    /* # this assumes time format has NO SPACE between time and AM/PM marks. */
                    if (dateTimeParts[1].indexOf(' ') == -1 && dateTimeParts[2] === undefined) {
                        var theTime = dateTimeParts[1];

                        /* # strip out all except numbers and colon */
                        var ampm = theTime.replace(/[0-9:]/g, '');

                        /* # strip out all except letters (for AM/PM) */
                        var time = theTime.replace(/[[^a-zA-Z]/g, '');

                        if (ampm == 'PM') {
                            time = time.split(':');

                            if (time[0] == 12) {
                                time = parseInt(time[0]) + ':' + time[1] + ':00';
                            } else {
                                time = parseInt(time[0]) + 12 + ':' + time[1] + ':00';
                            }
                        } else { /* if AM */

                            time = time.split(':');

                            if (time[0] < 10) {
                                time = '0' + time[0] + ':' + time[1] + ':00';
                            } else {
                                time = time[0] + ':' + time[1] + ':00';
                            }
                        }
                    }
                    var date = new Date(dateTimeParts[0] + 'T' + time);

                    return date;
                },
                checkIsSlotsAvailable: function () {

                },
                refreshVars: function () {
                    this.currentDate = this.startDate;
                },
                generateClass: function (name) {
                    return name.replace(/\s+/g, '-').toLowerCase();
                },
                getSelecttedTImeSot:function()
                {
                    $(".selected-slots").remove();
                    var elem = event.target || event.srcElement || event.currentTarget;
                    if (typeof elem !== 'undefined') {
                        $('#' + elem.id + '_time').val(elem.getAttribute('value'));
                        $('#' + elem.id + '_date').val(elem.getAttribute('data-date'));

                        if (model.selectedSlots().length == 0) {
                            model.selectedSlots.push({
                                'id': seller.id,
                                'name': seller.name,
                                'slot_time': elem.getAttribute('value'),
                                'date': elem.getAttribute('data-date'),
                                'slot_id': elem.id
                            });
                            model.sellerCount(model.sellerCount() + 1);
                        } else {
                            let flag=1;
                            $.each(model.selectedSlots(),function (index, value) {
                                if (seller.id == value.id) {
                                    model.selectedSlots()[index].slot_time = elem.getAttribute('value');
                                    model.selectedSlots()[index].date = elem.getAttribute('data-date');
                                    model.selectedSlots()[index].slot_id = elem.id;
                                    flag=0;
                                }
                            });
                            
                        }
                    }
                    customerData.set("selected-slots", model.selectedSlots());
                    model.isChecked(true);
                    $('#co-shipping-method-form').append("<input class='selected-slots' type='hidden' name='seller_data' value='" + JSON.stringify(model.selectedSlots()) + "'/>");
                    return true;
                },
                selectTimeSlot: function (model, seller, data, event) {
                    $(".selected-slots").remove();
                    var elem = event.target || event.srcElement || event.currentTarget;
                    if (typeof elem !== 'undefined') {
                        $('#' + elem.id + '_time').val(elem.getAttribute('value'));
                        $('#' + elem.id + '_date').val(elem.getAttribute('data-date'));

                        if (model.selectedSlots().length == 0) {
                            model.selectedSlots.push({
                                'id': seller.id,
                                'name': seller.name,
                                'slot_time': elem.getAttribute('value'),
                                'date': elem.getAttribute('data-date'),
                                'slot_id': elem.id
                            });
                            model.sellerCount(model.sellerCount() + 1);
                        } else {
                            let flag=1;
                            $.each(model.selectedSlots(),function (index, value) {
                                if (seller.id == value.id) {
                                    model.selectedSlots()[index].slot_time = elem.getAttribute('value');
                                    model.selectedSlots()[index].date = elem.getAttribute('data-date');
                                    model.selectedSlots()[index].slot_id = elem.id;
                                    flag=0;
                                }
                            });
                            if (flag) {
                                model.selectedSlots.push({
                                    'id': seller.id,
                                    'name': seller.name,
                                    'slot_time': elem.getAttribute('value'),
                                    'date': elem.getAttribute('data-date'),
                                    'slot_id': elem.id
                                });
                                model.sellerCount(model.sellerCount() + 1);
                            }
                        }
                    }
                    customerData.set("selected-slots", model.selectedSlots());
                    model.isChecked(true);
                    $('#co-shipping-method-form').append("<input class='selected-slots' type='hidden' name='seller_data' value='" + JSON.stringify(model.selectedSlots()) + "'/>");
                    return true;
                },
                saveShippingInformation: function () {
                    var payload;
    
                    if (!quote.billingAddress()) {
                        selectBillingAddressAction(quote.shippingAddress());
                    }
    
                    payload = {
                        addressInformation: {
                            shipping_address: quote.shippingAddress(),
                            billing_address: quote.billingAddress(),
                            shipping_method_code: quote.shippingMethod().method_code,
                            shipping_carrier_code: quote.shippingMethod().carrier_code,
                            extension_attributes:{
                                seller_data: $('[name="seller_data"]').val(),
                            }
                        }
                    };
    
                    fullScreenLoader.startLoader();
    
                    return storage.post(
                        resourceUrlManager.getUrlForSetShippingInformation(quote),
                        JSON.stringify(payload)
                    ).done(
                        function (response) {
                            quote.setTotals(response.totals);
                            paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                            fullScreenLoader.stopLoader();
                        }
                    ).fail(
                        function (response) {
                            errorProcessor.process(response);
                            fullScreenLoader.stopLoader();
                        }
                    );
                }
            }
        )
    }
);
