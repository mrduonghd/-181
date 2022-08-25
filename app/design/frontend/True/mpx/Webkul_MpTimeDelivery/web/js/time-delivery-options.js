/**
 * Mpx Software
 *
 * @category  Mpx
 * @package   Mpx_MpTimeDelivery
 * @author    Mpx
 */
define(
    [
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'jquery/jquery-ui-timepicker-addon',
    'mage/translate',
    'mage/backend/validation',
    'Magento_Ui/js/modal/modal'
    ],
    function ($, mageTemplate, alert) {
        'use strict';

        $.widget(
            'mage.timeDeliveryOptions',
            {
                options: {
                    selectionItemCount: {}
                },

                _create: function () {
                    this.baseTmpl = mageTemplate('#time-delivery-base-template');
                    this._initOptionBoxes();
                    this._addValidation();
                },
                _addValidation: function () {
                    $.validator.addMethod(
                        'required-option-select',
                        function (value) {
                            return (value !== '');
                        },
                        $.mage.__('Select day for delivery.')
                    );
                },
                _initOptionBoxes: function () {
                    this._on(
                        {

                            /**
                             * Remove custom option or option row for 'select' type of custom option
                             */
                            'click button[id^=time_delivery_][id$=_delete]': function (event) {
                                alert(
                                    {
                                        title: '',
                                        modalClass: 'alert',
                                        content: $.mage.__('Are you sure you want to delete?'),
                                        actions: {
                                            always: function () {
                                            }
                                        },
                                        buttons: [{
                                            text: $.mage.__('Cancel'),
                                            class: 'action',

                                            /**
                                             * Click handler.
                                             */
                                            click: function () {
                                                this.closeModal(true);
                                            }
                                        }, {
                                            text: $.mage.__('Yes'),
                                            class: 'action primary accept',

                                            /**
                                             * Click handler.
                                             */
                                            click: function () {
                                                var element = $(event.target).closest('#product_options_container_top > div.fieldset-wrapper');
                                                if (element.length) {
                                                    $('#time_delivery_' + element.attr('id').replace('option_', '') + '_is_delete').val(1);
                                                    element.addClass('ignore-validate').hide();
                                                    this.closeModal(true);
                                                }
                                            }
                                        }]
                                    }
                                );
                            },
                            /**
                             * Add new time slot
                             */
                            'click #add_new_defined_slot': function (event) {
                                this.addSlot(event);
                            },
                            'focusout input[id^=time_delivery_][id$=_quota]': function (event) {
                                var element = $(event.target).closest('#product_options_container_top > div.fieldset-wrapper');
                                var start_time = $('#start_time_delivery_'+element.attr('id').replace('option_','')).val();
                                var end_time = $('#end_time_delivery_'+element.attr('id').replace('option_','')).val();
                                if (Date.parse("1-1-2000 " + start_time) > Date.parse("1-1-2000 " + end_time)) {
                                    alert(
                                        {
                                            content: $.mage.__('Invalid Time Interval.'),
                                            actions: {
                                                always: function () {
                                                    $('#start_time_delivery_'+element.attr('id').replace('option_','')).focus();
                                                    $(event.target).val('');
                                                }
                                            }
                                        }
                                    );
                                }
                            },

                        }
                    );
                },
                /**
                 * Add custom option
                 */
                addSlot: function (event) {
                    var data = {},
                    element = event.target || event.srcElement || event.currentTarget,
                    baseTmpl;

                    if (typeof element !== 'undefined') {
                        data.id = this.options.itemCount;
                        data.type = '';
                        data.seller_id = this.options.sellerId;
                    } else {
                        data = event;
                        this.options.itemCount = data.id;
                    }

                    baseTmpl = this.baseTmpl(
                        {
                            data: data
                        }
                    );

                    $(baseTmpl)
                    .appendTo(this.element.find('#product_options_container_top'));




                    if (typeof element !== 'undefined') {
                        $('#' + this.options.fieldId + '_' + data.id + '_quota').val(99999999);
                    }
                    var startTimeTextBox = $('#start_time_delivery_'+data.id);
                    var endTimeTextBox = $('#end_time_delivery_'+data.id);
                    $.timepicker.datetimeRange(
                        startTimeTextBox,
                        endTimeTextBox,
                        {
                            timeOnly: true,
                            timeFormat: 'hh:mm TT',
                            controlType: 'select',
                            start: {},
                            end: {}
                        }
                    );
                    if (data.day) {
                        $('#' + this.options.fieldId + '_' + data.id + '_type').val(data.day).trigger('change', data);
                    }
                     /*set quota value if set */
                    if (data.quota) {
                        $('#' + this.options.fieldId + '_' + data.id + '_quota').val(data.quota);
                    }
                    /* set selected start hh and mm value if set */
                    if (data.start) {
                        $('#start_' + this.options.fieldId + '_' + data.id).val(data.start).trigger('change', data);
                    }
                    /*set selected end hh and mm value if set */
                    if (data.end) {
                        $('#end_' + this.options.fieldId + '_' + data.id).val(data.end).trigger('change', data);
                    }
                    this.options.itemCount++;
                },
            }
        );
    }
);
