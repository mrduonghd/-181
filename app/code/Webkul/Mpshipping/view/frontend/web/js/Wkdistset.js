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
    $.widget('mage.Wkdistset', {
        options: {
            deleteData: $t("Are you sure, you want to delete?"),
            deleteAllData: $t("Are you sure, you want to delete these Shipping Rule(s)?"),
            editData: $t("Are you sure, you want to edit?")
        },
        _create: function () {
            var self = this;
            var addNewSetForm = $(self.options.addNewSetForm);
            var editRateForm = $(self.options.editRateForm);
            addNewSetForm.mage('validation', {});
            editRateForm.mage('validation', {});
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
                                $('#distancefrom').attr('value',JSONArray.dist_from);
                                $('#distanceto').attr('value',JSONArray.dist_to);
                                $('#shippingmethod').attr('value',JSONArray.method_name);
                                $('#wkprice').attr('value',JSONArray.price);
                                $('#editRate').attr('action',self.options.editUrl+"id/"+id);
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
                                $('#form-distancesetlist-massdelete').submit();
                            },
                        }
                    });
                }
            });
        }
    });
    return $.mage.Wkdistset;
});
