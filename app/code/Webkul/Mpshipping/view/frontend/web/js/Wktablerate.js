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
    $.widget('mage.Wktablerate', {
        options: {
            ajaxErrorMessage: $t('There is some error during executing this process, please try again later.'),
            deleteData: $t("Are you sure, you want to delete?"),
            editData: $t("Are you sure, you want to edit?"),
            invalidFile: $t("Invalid File Extension"),
            addRecordText: $t("Add shipping record")
        },
        _create: function () {
            var self = this;
            var addNewRateForm = $(self.options.addNewRate);
            addNewRateForm.mage('validation', {});
            $(self.options.wk_ship_method_delete).on('click', function () {
                var shipemthodid=$(this).parents('.shipping_method_head').find('.shipmethod_id').attr('value');
                var dicision = confirm({
                    content: self.options.deleteData,
                    actions: {
                        confirm: function () {
                            window.location=self.options.deleteUrl+"id/"+shipemthodid;
                        },
                    }
                });
            });
            $(self.options.wkCloseWrap).on('click',function () {
                $(self.options.wkRateWrap).hide();
                $('.top-container').css('z-index','10');
            });
            $(self.options.addShippingLink).on('click', function () {
                $(self.options.addShippingLink).hide();
                $(self.options.addShippingForm).show();
            });
            $(self.options.deleteRate).on('click',function () {
                var id = $(this).parents('.wk_row_list').find('.hidden_id').val();
                var dicision = confirm({
                    content: self.options.deleteData,
                    actions: {
                        confirm: function () {
                            window.location=self.options.deleteRateUrl+"id/"+id;
                        },
                    }
                });
            });
            $('#uploadCsv').change(function () {
                var filename = $(this).val();
                var file = filename.split(".");
                file = file[1];
                if (file != 'csv') {
                    alert({
                        content: self.options.invalidFile
                    });
                    $(this).val('');
                }
            });
            $('#is_range').change(function () {
              var value = $(this).val();
              if (value == 'yes') {
                $('#zipcode').removeClass('required-entry');
              } else {
                $('#zipcode').addClass('required-entry');
              }
            });
            $('.mpship_edit').on('click',function () {
                var id = $(this).parents('.wk_row_list').find('.hidden_id').val();
                var JSONArray = $.parseJSON($(this).parents('.wk_row_list').find('.data').val());
                var dicision = confirm({
                    content: self.options.editData,
                    actions: {
                        confirm: function () {
                            $.each(JSONArray,function (key,value) {
                                $('#country_code').attr('value',JSONArray.country_code);
                                $('#region_code').attr('value',JSONArray.region_code);
                                $('#weight_from').attr('value',JSONArray.weight_from);
                                $('#weight_to').attr('value',JSONArray.weight_to);
                                $('#zip_from').attr('value',JSONArray.zip_from);
                                $('#zip_to').attr('value',JSONArray.zip_to);
                                $('#price').attr('value',JSONArray.price);
                                $('#is_range').attr('value',JSONArray.is_range);
                                $('#zipcode').attr('value',JSONArray.zipcode);
                                $('#addNewRate').attr('action',self.options.editUrl+"id/"+id);
                                $('.wk_mp_design h4').html('<h4>Edit record</h4>');
                                $('.top-container ').css('z-index','-10');
                                if (JSONArray.is_range == 'yes') {
                                  $('#zipcode').removeClass('required-entry');
                                } else {
                                  $('#zipcode').addClass('required-entry');
                                }
                                $('.add_shipping').hide();
                                $('.add_shipping_form').show();
                                $('.shipping_method').attr('value',JSONArray.shipping_method);
                                $('.wk_shipping_rate_wrapper').show();
                            });
                        },
                    }
                });
            });
            $('.addnewshipping').on('click',function () {
                $('#addNewRate').attr('action',self.options.addNewUrl);
                $('#addNewRate').closest('form').find("input[type=text], textarea").val("");
                $('.wk_mp_design h4').html('<h4>'+self.options.addRecordText+'</h4>');
                $('.wk_shipping_rate_wrapper').show();
                $('.add_shipping').show();
                $('.add_shipping_form').hide();
                $('.top-container ').css('z-index','-10');

            });
            $(".shipping_suggestion_outer .shipping_method").on('focus', function () {
                $(".wk_sugestion_list").show();
            }).on('focusout', function () {
                $(".wk_sugestion_list").hide();
            });
            $(".shipping_suggestion_outer span").on('mousedown', function (event) {
                event.preventDefault();
            }).on('click', function () {
                if ($(this).attr('value')!='') {
                    $(".shipping_suggestion_outer .shipping_method").val($(this).text());
                    $('#country_code').trigger('focus');
                }
            });
            $('#save_butn').on('click', function (e) {
                e.preventDefault();
                if ($("#addNewRate").valid()!==false) {
                    if ($('.shipping_method').valid()!=0) {
                        $("#addNewRate").submit();
                    } else {
                        if ($('#shipping_method-error').length > 0) {
                            var errorhtml = $('#shipping_method-error');
                            $('#shipping_method-error').remove();
                            $('.add_shipping_outer').append(errorhtml);
                            errorhtml.show();
                        }
                    }
                }
                return false;
            });
        }
    });
    return $.mage.Wktablerate;
});
