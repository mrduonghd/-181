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
    'mage/translate',
    "jquery/ui",
    ],
    function ($, ko, Component, customerData) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    template: 'Webkul_MpTimeDelivery/seller-time-dropdown'
                },
                initObservable: function () {
                    this._super()
                        .observe([
                            'daySelect',
                        ]);
                    return this;
                },
                totalSellerCount: ko.observable(0),
                sellerCount: ko.observable(0),
                selectedSlots: ko.observableArray([]),
                initialize: function () {
                    
                    this._super();
                    var self = this;
                    this.allowedDays = window.checkoutConfig.allowed_days;
                    this.defaultAllowedDay = window.checkoutConfig.defaultDay;
                    this.defaultStartTime = window.checkoutConfig.defaultStartTime;
                    this.defaultEndTime = window.checkoutConfig.defaultEndTime;
                    this.isEnabled = window.checkoutConfig.isEnabled;
                    this.sellersData = window.checkoutConfig.seller;
                    this.startDate = window.checkoutConfig.start_date;
                    this.Selectdata=ko.observable();
                    this.slots = ko.observableArray([]);
                    this.availableSlots = ko.observableArray([]);
                    this.timeslots=ko.observableArray([]);
                    this.sortedSlots = ko.observableArray([]),
                    this.chosenDate=ko.observableArray([]),
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
                        // for(var p in Selectdata)
                        // {
                        //     if(cday!=undefined && cday==p)
                        //     {
                        //         //$("#wk_slot_times_"+selectedId).html($.mage.__("<option>Choose Time Slot</option>"));
                        //         for(var p1 in Selectdata[p])
                        //         {
                        //             if(Selectdata[p][p1].is_available)
                        //             {
                        //                 // var data_date=this.getDate(data_id ,cday)
                        //                  option+=$.mage.__("<option id='"+Selectdata[p][p1].slot_id+"' name='id_"+data_id+"' data_date='"+data_date+"' value='"+$.mage.__(Selectdata[p][p1].slot)+"'>"+$.mage.__(Selectdata[p][p1].slot)+"</option>");
                        //             }
                        //         }
                        //     }
                        // }
                        // $("#wk_slot_times_"+selectedId).append(option);
                     
                    }     
                },
                getDefaultDate:function()
                {
                    var inMiSec= this.chosenDate();
                   var returnDate = $.datepicker.formatDate(
                        'DD, d MM, yy',
                        new Date(inMiSec[0])
                    );
                    return returnDate;
                },
                getDefaultDay:function()
                {
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
                toConvertTime:function(time) {
                    // Check correct time format and split into components
                    time = time.toString ().match (/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];
                  
                    if (time.length > 1) { // If time format correct
                      time = time.slice (1);  // Remove full string match value
                      time[5] = +time[0] < 12 ? ' AM' : ' PM'; // Set AM/PM
                      time[0] = +time[0] % 12 || 12; // Adjust hours
                    }
                    return time.join (''); // return adjusted time or original string
                  },
                getDefaultEndTime:function()
                {
                    if(this.defaultEndTime!="")
                    {
                        var tm=this.defaultEndTime.split(",").join(":");    
                        this.defaultEndTime=this.toConvertTime(tm);
                    }
                    return this.defaultEndTime;
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
                    if(cday!=undefined)
                    {
                        for (var i = 0; i <= this.maxDays; i++) {
                            var nDate = new Date(sellerStartDate);

                            nDate.setDate(nDate.getDate() + check);
                            var day = nDate.getDate();
                            var month = nDate.getMonth() + 1;
                            if (day < 10) {
                                day = "0" + day;
                            }
                            if (month < 10) {
                                month = "0" + month;
                            }
                            if(cday!=undefined)
                            {
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
                        }
                    }
                    return returnDate;
                },
                checkDay: function (day, sellerStart,selectedId,dataSlot) {

                   // this.getDefaultSelected(day,selectedId,dataSlot);
                    if (sellerStart) {
                        var d = new Date(sellerStart);
                    } else {
                        var d = new Date(this.startDate);
                    }
                    // $('#wk_days_option'+localStorage.getItem('SelectDateValue')).attr("selected","selected");
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
                getFormatDate:function(date){

                    var tm= date.split("-");
                    var year=$.mage.__('年');
                    var month=$.mage.__('月');
                    var day=$.mage.__('日');
                    return tm[0]+year+tm[1]+month+tm[2]+day;
                },
                daySelect:function(Select_data,SelectId,event)
                {
                  
                    var data_id=Select_data.id;
                    var Selectdata=Select_data.slots;
                    var SelectDateValue='';
                    var count = 0;
                    if(event!=undefined)
                    {
                     
                        var elem = event.target || event.srcElement || event.currentTarget;
                        
                        if (typeof elem !== 'undefined' && (elem instanceof jQuery && elem.length) || elem instanceof HTMLElement ) {
                            
                            SelectId = elem.name.split('_')[1];
                            var elem = event.currentTarget;
                            if(elem.id.indexOf('wk_days_slot') != -1){
                                if (typeof elem !== 'undefined') {
                                    var selectValue=$('#' + elem.id).val();
                                        if(selectValue!='' ){
                                    
                                                if(selectValue!='None' )
                                                {   
                                                    var a=selectValue.split("年");
                                                    var yy=a[0];
                                                    var b=a[1].split("月");
                                                    var mm=b[0];
                                                    var c=b[1].split("日");
                                                    var dd=c[0];
                                                    var nowDate=yy+"-"+mm+"-"+dd;
                                                    SelectDateValue = $.datepicker.formatDate(
                                                        'yy-mm-dd',
                                                        new Date(nowDate)
                                                    );
                                                    this.Selectdata(SelectDateValue);
                                                }else{
                                                    SelectDateValue = $.mage.__('None');

                                                }
                                        }else{
                                            SelectDateValue = '';
                                        }

                                } else{
                                    this.Selectdata(0);
                                }   
                            
                            
                                if(SelectDateValue!='')
                                {
                                
                                        if(SelectDateValue!=$.mage.__('None'))
                                        {

                                                var option="";
                                                for(var p in Selectdata)
                                                {
                                                    if(SelectDateValue!=undefined && SelectDateValue==p)
                                                    {
                                                    //  $("#wk_slot_times_"+SelectId).html("<option>"+$.mage.__('Choose Time Slot')+"</option>");
                                                    $("#wk_slot_times_"+SelectId).html("");
                                                    
                                                        for(var p1 in Selectdata[p])
                                                        {
                                                            if(Selectdata[p][p1].is_available)
                                                            {
                                                                var data_date=this.getDate(data_id ,SelectDateValue)
                                                                option+="<option id='"+Selectdata[p][p1].slot_id+"' name='id_"+data_id+"' data_date='"+data_date+"' value='"+$.mage.__(Selectdata[p][p1].slot)+"'>"+$.mage.__(Selectdata[p][p1].slot)+"</option>";
                                                            }
                                                        }
                                                        
                                                    }
                                                }
                                                $('.' + elem.getAttribute('seller-group')).removeClass('selected');
                                                $(event.currentTarget).addClass('selected');
                                                $("#wk_slot_times_"+SelectId).append(option);
                                        }else{
                                            $("#wk_slot_times_"+SelectId).html("<option value='"+$.mage.__('None')+"'>"+$.mage.__('None')+"</option>");
                                        }
                                }else if(SelectDateValue==$.mage.__('None'))
                                {
                                    $("#wk_slot_times_"+SelectId).html("<option value='"+$.mage.__('None')+"'>"+$.mage.__('None')+"</option>");
                                }
                            }
                        } else {
                            //this is for  when page load
                            var customerLoadData= customerData.get('changeevent-slots')();
                            $('#co-shipping-method-form').append("<input class='selected-slots' type='hidden' name='seller_data' value='" + JSON.stringify(customerLoadData) + "'/>");
                            var option = "";
                            setTimeout(function(){
                            var customData = [];
                            $.each(customerLoadData,function(index,value){
                                 $("#wk_days_option_"+value.id+"_"+value.date).attr("selected","selected");
                               
                                
                            });
                            //get selected date on load
                            var selectedDate = $("#wk_days_slot_"+SelectId).find(":selected").attr('keydata');
                            //add time slot on page load
                            if(Selectdata[selectedDate]!=undefined){
                                var optionData ="";
                                $.each(Selectdata[selectedDate],function(splindex,splvalue){
                                    optionData+="<option id='"+splvalue.slot_id+"' name='id_"+SelectId+"' data_date='"+selectedDate+"' value='"+$.mage.__(splvalue.slot)+"'>"+$.mage.__(splvalue.slot)+"</option>";
                                });
                                
                                $("#wk_slot_times_"+SelectId).html("");
                                $("#wk_slot_times_"+SelectId).append(optionData);
                                $.each(customerLoadData,function(index,value){
                                    $("#"+value.slot_id).attr("selected","selected");
                                  
                                   
                               });
                            }
                        },1000);
                             
                        }
                        var eventData = false;
                        if(event.type=="change"){
                            eventData = true;
                        }
                        this.selectTimeSlot(Select_data,null,null,undefined,$("#wk_slot_times_"+SelectId),eventData);
                    }  
                    
                },
                getSelectedDate:function()
                {
                    return this.Selectdata();
                },
                checkIsSlotsAvailable: function () {
                },
                refreshVars: function () {
                    this.currentDate = this.startDate;
                },
                generateClass: function (name) {
                    return name.replace(/\s+/g, '-').toLowerCase();
                },
                isSelected: function (model, seller, data, event) {
                    if ($(event.currentTarget).hasClass('disabled') == false) {
                        var elem = event.currentTarget;
                        $('.' + elem.getAttribute('seller-group')).removeClass('selected');
                        $(event.currentTarget).addClass('selected');
                    }

                },
                selectTimeSlot: function (seller, model, data, event,elem,eventData=true) {
                    $(".selected-slots").remove();
                    if(event!=undefined)
                    {
                        var elem = event.target || event.srcElement || event.currentTarget;

                        if (typeof elem !== 'undefined' && (elem instanceof jQuery && elem.length) || elem instanceof HTMLElement) {
                            var wkTimeSlot =  $("select[id^=wk_slot_times_").val();
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
                                if(elem.id.indexOf('wk_slot_times') != -1){
                                    var customerLoadData= customerData.get('changeevent-slots')();
                                
                                    $.each(customerLoadData,function(index, value){
                                        if (seller.id == value.id) {
                                            customerLoadData[index].slot_time = Selected_value;
                                            customerLoadData[index].date = Selected_date;
                                            customerLoadData[index].slot_id = Selected_id;
                                        }
                                    })
                                    customerData.set("changeevent-slots", customerLoadData);
                               }
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
                                var Selected_value = $.mage.__(
                                  $("select[id^=wk_slot_times_").val()
                                );
                                if (Selected_value == undefined) {
                                    Selected_value = $.mage.__('None');
                                }
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
                                var customerLoadData= customerData.get('changeevent-slots')();
                                if(customerLoadData.length){
                                    $('#co-shipping-method-form').append("<input class='selected-slots' type='hidden' name='seller_data' value='" + JSON.stringify(customerLoadData) + "'/>");
                                } else{
                                    $('#co-shipping-method-form').append("<input class='selected-slots' type='hidden' name='seller_data' value='" + JSON.stringify(this.selectedSlots()) + "'/>");
                                }

                        
                        }
                    }else 
                    if(elem)
                    {
                        if($(elem).val()!=null && $(elem).val()!="None" && eventData==true){
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
                            customerData.set(
                              "changeevent-slots",
                              this.selectedSlots()
                            );
                            customerData.set("selected-slots", this.selectedSlots());
                            this.isChecked(true);
                            $('#co-shipping-method-form').append("<input class='selected-slots' type='hidden' name='seller_data' value='" + JSON.stringify(this.selectedSlots()) + "'/>");
                        
                        }
                    }
                    return true;
                },
                ChangeDynamicTimeSlot: function (model,event, seller) {
                    $(".selected-slots").remove();
                    var elem = event.target || event.srcElement || event.currentTarget;
                    if (typeof elem !== 'undefined') {
                        $('#' + elem.id + '_time').val(elem.getAttribute('value'));
                        $('#' + elem.id + '_date').val(elem.getAttribute('data_date'));

                        if (model.selectedSlots().length == 0) {
                            model.selectedSlots.push({
                                'id': seller.id,
                                'name': seller.name,
                                'slot_time': elem.getAttribute('value'),
                                'date': elem.getAttribute('data_date'),
                                'slot_id': elem.id
                            });
                            model.sellerCount(model.sellerCount() + 1);
                        } else {
                            let flag=1;
                            $.each(model.selectedSlots(),function (index, value) {
                                if (seller.id == value.id) {
                                    model.selectedSlots()[index].slot_time = elem.getAttribute('value');
                                    model.selectedSlots()[index].date = elem.getAttribute('data_date');
                                    model.selectedSlots()[index].slot_id = elem.id;
                                    flag=0;
                                }
                            });
                            if (flag) {
                                model.selectedSlots.push({
                                    'id': seller.id,
                                    'name': seller.name,
                                    'slot_time': elem.getAttribute('value'),
                                    'date': elem.getAttribute('data_date'),
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
                }
                
            }
        )
    }
);
