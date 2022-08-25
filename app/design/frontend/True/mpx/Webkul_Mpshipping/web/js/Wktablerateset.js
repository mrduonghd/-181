/**
 * Mpshipping
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    "jquery/ui"
], function ($, $t, alert, confirm) {
    'use strict';
    $.widget('mage.Wktablerateset', {
        options: {
            deleteData: $t("Are you sure, you want to delete?"),
            deleteAllData: $t("Are you sure, you want to delete these Shipping sets?"),
            editData: $t("Are you sure, you want to edit?"),
            editRecordText: $t("Edit shipping set record")

        },
        _create: function () {
            var self = this;
            var addNewSetForm = $(self.options.addNewSetForm);
            var editRateForm = $(self.options.editRateForm);
            addNewSetForm.mage('validation', {});
            editRateForm.mage('validation', {});
            var shippingType = $(self.options.wkShippingType).val();
            if (shippingType == 'free') {
                $(self.options.wkPrice).removeClass('required-entry');
                $(self.options.ShippingPrice).hide();
            }
            $(self.options.wkShippingType).on('change',function (e) {
                var value = $(this).val();
                if (value == 'free') {
                    $(self.options.ShippingPrice).hide();
                } else {
                    $(self.options.ShippingPrice).show();
                }
            })
            $('.mpship_edit').on('click',function () {
                var id = $(this).parents('.wk_row_list').find('.hidden-id').val();
                var JSONArray = $.parseJSON($(this).parents('.wk_row_list').find('.data').val());
                var dicision = confirm({
                    content: self.options.editData,
                    actions: {
                        confirm: function () {
                            $.each(JSONArray,function (key,value) {
                                $('#pricefrom').attr('value',JSONArray.price_from);
                                $('#priceto').attr('value',JSONArray.price_to);
                                if (JSONArray.shipping_type == 'free') {
                                    $('#wkprice').parents('li').hide();
                                }
                                $('#shippingtype').attr('value',JSONArray.shipping_type);
                                $('#shippingmethod').attr('value',JSONArray.method_name);
                                $('#wkprice').attr('value',JSONArray.price);
                                $('#editRate').attr('action',self.options.editUrl+"id/"+id);
                                $('.wk_mp_design h4').html('<h4>'+self.options.editRecordText+'</h4>');
                                $('.top-container ').css('z-index','-10');
                                $('.wk_shipping_rate_wrapper').show();
                            });
                        },
                    }
                });
            });
            $(self.options.deleteSetRate).on('click',function () {
                var id = $(this).parents('.wk_row_list').find('.hidden-id').val();
                var dicision = confirm({
                    content: self.options.deleteData,
                    actions: {
                        confirm: function () {
                            window.location=self.options.deleteUrl+"id/"+id;
                        },
                    }
                });
            });
            $("#shippingtype").on('change',function () {
                var shippingType = $(this).val();
                if (shippingType == "free") {
                    $('#wkprice').parents('li').hide();
                } else {
                    $('#wkprice').parents('li').show();
                }
            });
            $(self.options.wkCloseWrap).on('click',function () {
                $(self.options.wkRateWrap).hide();
                $('.top-container').css('z-index','10');
            });
            $('#mpselecctall').click(function (event) {
                if (this.checked) {
                    $('.mpcheckbox').each(function () {
                        this.checked = true;
                    });
                } else {
                    $('.mpcheckbox').each(function () {
                        this.checked = false;
                    });
                }
            });
            $('#mass-delete-butn').click(function (e) {
                var flag =0;
                e.preventDefault();
                $('.mpcheckbox').each(function () {
                    if (this.checked === true) {
                        flag =1;
                    }
                });
                if (flag === 0) {
                    alert({content : $t(' No Checkbox is checked ')});
                    return false;
                } else {
                    var dicision = confirm({
                        content: self.options.deleteAllData,
                        actions: {
                            confirm: function () {
                                $('#form-shippingsetlist-massdelete').submit();
                            },
                        }
                    });
                }
            });
        }
    });
    return $.mage.Wktablerateset;
});
