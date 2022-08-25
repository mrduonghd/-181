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
                    template: 'Webkul_MpTimeDelivery/seller-time-day-slot'
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
                getDefaultSelected:function(cday,selectedId,Selectdata)
                {
                    var cDate = new Date(cday);
                    var weekdays = new Array(7);
                    weekdays[0] = "Sunday";
                    weekdays[1] = "Monday";
                    weekdays[2] = "Tuesday";
                    weekdays[3] = "Wednesday";
                    weekdays[4] = "Thursday";
                    weekdays[5] = "Friday";
                    weekdays[6] = "Saturday";
                    var r = weekdays[cDate.getDay()];
                    var data_id=Selectdata.id;
                    Selectdata=Selectdata.slots;
                    if(r==this.defaultAllowedDay)
                    {
                        this.chosenDate([cday]);
                        var option="";
                        for(var p in Selectdata)
                        {
                            if(cday!=undefined && cday==p)
                            {
                                //$("#wk_slot_times_"+selectedId).html($.mage.__("<option>Choose Time Slot</option>"));
                                for(var p1 in Selectdata[p])
                                {
                                    if(Selectdata[p][p1].is_available)
                                    {
                                        var data_date=this.getDate(data_id ,cday)
                                         option+=$.mage.__("<option id='"+Selectdata[p][p1].slot_id+"' name='id_"+data_id+"' data_date='"+data_date+"' value='"+$.mage.__(Selectdata[p][p1].slot)+"'>"+$.mage.__(Selectdata[p][p1].slot)+"</option>");
                                    }
                                }
                            }
                        }
                        $("#wk_slot_times_"+selectedId).append(option);
                     
                    }     
                },
                getDefaultDate:function()
                {
                    var inMiSec= this.chosenDate();
                    console.log(inMiSec[0]);
                   var returnDate = $.datepicker.formatDate(
                        'DD, d MM, yy',
                        new Date(inMiSec[0])
                    );
                    return returnDate;
                },
                getDefaultDay:function()
                {
                  console.log(this.defaultAllowedDay);
                   return this.defaultAllowedDay;
                },
                getDefaultTimeRange:function()
                {
                    return this.getDefaultStartTime()+"-"+this.getDefaultEndTime();
                },
                getDefaultStartTime:function()
                {
                    if(this.defaultStartTime!="")
                    {
                        var tm=this.defaultStartTime.split(",").join(":");
                       
                        this.defaultStartTime=this.toConvertTime(tm);
                    }
                    return this.defaultStartTime;
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
                daySelect:function(Selectdata,SelectId,event)
                {
                    var data_id=Selectdata.id;
                    Selectdata=Selectdata.slots;
                    
                    if(event!=undefined)
                    {
                        var elem = event.target || event.srcElement || event.currentTarget;
                        if (typeof elem !== 'undefined' && (elem instanceof jQuery && elem.length) || elem instanceof HTMLElement) {
                    
                            var elem = event.currentTarget;
                        if (typeof elem !== 'undefined') {
                            var selectValue=$('#' + elem.id).val();
                            var SelectDateValue = $.datepicker.formatDate(
                                'yy-mm-dd',
                                new Date(selectValue)
                            );
                            this.Selectdata(SelectDateValue);
                        } else{
                            this.Selectdata(0);
                        }   
                        
                        var option="";
                        for(var p in Selectdata)
                        {
                            if(SelectDateValue!=undefined && SelectDateValue==p)
                            {
                                $("#wk_slot_times_"+SelectId).html("<option>Choose Time Slot</option>");
                                for(var p1 in Selectdata[p])
                                {
                                    if(Selectdata[p][p1].is_available)
                                    {
                                        var data_date=this.getDate(data_id ,SelectDateValue)
                                         option+="<option id='"+Selectdata[p][p1].slot_id+"' name='id_"+data_id+"' data_date='"+data_date+"' value='"+Selectdata[p][p1].slot+"'>"+Selectdata[p][p1].slot+"</option>";
                                    }
                                }
                                
                            }
                        }
                        // $('.' + elem.getAttribute('seller-group')).removeClass('selected');
                        // $(event.currentTarget).addClass('selected');
                        $("#wk_slot_times_"+SelectId).append(option);
                    }
                }
                    
                },
                getCheckTime: function (time, date) {
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
                        }
                    }
                    customerData.set("selected-slots", model.selectedSlots());
                    model.isChecked(true);
                    $('#co-shipping-method-form').append("<input class='selected-slots' type='hidden' name='seller_data' value='" + JSON.stringify(model.selectedSlots()) + "'/>");
                    return true;
                },
                selectTimeSlot: function (seller, model, data, event,elem) {
                    $(".selected-slots").remove();
                    
                    if(event!=undefined)
                    {
                        var elem = event.target || event.srcElement || event.currentTarget;
                    
                        if (typeof elem !== 'undefined' && (elem instanceof jQuery && elem.length) || elem instanceof HTMLElement) {

                          //  console.log($('#' + elem.id + '_time'))
                            //console.log($('#' + elem.id + '_date'));
                            var Selected_value=$.mage.__(elem.value);
                            var Selected_date=null;
                            var Selected_id=null;
                            
                            $.each($(elem).find("option"),function (index, value) {

                                if(this.getAttribute("value")==Selected_value)
                                {
                                    Selected_date=this.getAttribute("data_date");
                                    Selected_id=this.id;
                                }
                            });
                            if(Selected_date!=null)
                            {

                            Selected_date = $.datepicker.formatDate(
                                'yy-mm-dd',
                                new Date(Selected_date)
                            );

                            Selected_date=this.getFormatDate(Selected_date);
                            }else{
                                Selected_date=$.mage.__('None');
                            }

                            $('#' + elem.id + '_time').val(Selected_value);
                            $('#' + elem.id + '_date').val(Selected_date);

                            if (this.selectedSlots().length == 0) {
                                this.selectedSlots.push({
                                    'id': seller.id,
                                    'name': seller.name,
                                    'slot_time': Selected_value,
                                    'date': Selected_date,
                                    'slot_id': Selected_id
                                });
                                this.sellerCount(this.sellerCount() + 1);
                            } else {
                                let flag=1;
                                var thisthis=this;
                                $.each(this.selectedSlots(),function (index, value) {
                                    if (seller.id == value.id) {
                                        thisthis.selectedSlots()[index].slot_time = Selected_value;
                                        thisthis.selectedSlots()[index].date = Selected_date;
                                        thisthis.selectedSlots()[index].slot_id = Selected_id;
                                        flag=0;
                                    }
                                });

                                if (flag) {
                                    this.selectedSlots.push({
                                        'id': seller.id,
                                        'name': seller.name,
                                        'slot_time': Selected_value,
                                        'date': Selected_date,
                                        'slot_id': Selected_id
                                    });
                                    this.sellerCount(this.sellerCount() + 1);
                                }
                            }
                        
                        customerData.set("selected-slots", this.selectedSlots());
                        this.isChecked(true);
                        $('#co-shipping-method-form').append("<input class='selected-slots' type='hidden' name='seller_data' value='" + JSON.stringify(this.selectedSlots()) + "'/>");
                        }else{
                        
                                var Selected_value=$.mage.__($("select[id^=wk_slot_times_").val());
                                var Selected_date=null;
                                var Selected_id=null;
                                $.each($("select[id^=wk_slot_times_").find("option"),function (index, value) {

                                    if(this.getAttribute("value")==Selected_value)
                                    {
                                        Selected_date=this.getAttribute("data_date");
                                        Selected_id=this.id;
                                    }
                                });
                                if(Selected_date!=null)
                                {

                                Selected_date = $.datepicker.formatDate(
                                    'yy-mm-dd',
                                    new Date(Selected_date)
                                );

                                Selected_date=this.getFormatDate(Selected_date);
                                }else{
                                    Selected_date=$.mage.__('None');
                                }

                                $('#' + elem.id + '_time').val(Selected_value);
                                $('#' + elem.id + '_date').val(Selected_date);

                                if (this.selectedSlots().length == 0) {
                                    this.selectedSlots.push({
                                        'id': seller.id,
                                        'name': seller.name,
                                        'slot_time': Selected_value,
                                        'date': Selected_date,
                                        'slot_id': Selected_id
                                    });
                                    this.sellerCount(this.sellerCount() + 1);
                                } else {
                                    let flag=1;
                                    var thisthis=this;
                                    $.each(this.selectedSlots(),function (index, value) {
                                        if (seller.id == value.id) {
                                            thisthis.selectedSlots()[index].slot_time = Selected_value;
                                            thisthis.selectedSlots()[index].date = Selected_date;
                                            thisthis.selectedSlots()[index].slot_id = Selected_id;
                                            flag=0;
                                        }
                                    });

                                    if (flag) {
                                        this.selectedSlots.push({
                                            'id': seller.id,
                                            'name': seller.name,
                                            'slot_time': Selected_value,
                                            'date': Selected_date,
                                            'slot_id': Selected_id
                                        });
                                        this.sellerCount(this.sellerCount() + 1);
                                    }
                                 }
                    
                                customerData.set("selected-slots", this.selectedSlots());
                                this.isChecked(true);
                                $('#co-shipping-method-form').append("<input class='selected-slots' type='hidden' name='seller_data' value='" + JSON.stringify(this.selectedSlots()) + "'/>");

                        
                        }
                    }else if(elem)
                    {

                        var Selected_value=$.mage.__($(elem).val());
                        var Selected_date=null;
                        var Selected_id=null;
                        $.each($("select[id^=wk_slot_times_").find("option"),function (index, value) {

                            if(this.getAttribute("value")==Selected_value)
                            {
                                Selected_date=this.getAttribute("data_date");
                                Selected_id=this.id;
                            }
                        });
                        if(Selected_date!=null)
                        {
                        Selected_date = $.datepicker.formatDate(
                            'yy-mm-dd',
                            new Date(Selected_date)
                        );

                        Selected_date=this.getFormatDate(Selected_date);
                        }else{
                            Selected_date=$.mage.__('None');
                        }

                        $('#' + elem.id + '_time').val(Selected_value);
                        $('#' + elem.id + '_date').val(Selected_date);

                        if (this.selectedSlots().length == 0) {
                            this.selectedSlots.push({
                                'id': seller.id,
                                'name': seller.name,
                                'slot_time': Selected_value,
                                'date': Selected_date,
                                'slot_id': Selected_id
                            });
                            this.sellerCount(this.sellerCount() + 1);
                        } else {
                            let flag=1;
                            var thisthis=this;
                            $.each(this.selectedSlots(),function (index, value) {
                                if (seller.id == value.id) {
                                    thisthis.selectedSlots()[index].slot_time = Selected_value;
                                    thisthis.selectedSlots()[index].date = Selected_date;
                                    thisthis.selectedSlots()[index].slot_id = Selected_id;
                                    flag=0;
                                }
                            });

                            if (flag) {
                                this.selectedSlots.push({
                                    'id': seller.id,
                                    'name': seller.name,
                                    'slot_time': Selected_value,
                                    'date': Selected_date,
                                    'slot_id': Selected_id
                                });
                                this.sellerCount(this.sellerCount() + 1);
                            }
                         }
            
                        customerData.set("selected-slots", this.selectedSlots());
                        this.isChecked(true);
                        $('#co-shipping-method-form').append("<input class='selected-slots' type='hidden' name='seller_data' value='" + JSON.stringify(this.selectedSlots()) + "'/>");
                        
                    }
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
