/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    'mage/calendar'
], function ($, $t, alert) {
    'use strict';

    var messageRequired = $t("Please select a category to register the product.");

    $.widget('mage.sellerAddProduct', {
        options: {
            errorMessageSku: $t("SKU can\'t be left empty"),
            ajaxErrorMessage: $t('There was error during fetching results.'),
            productid: 0
        },
        _create: function () {
            var self = this;
            $("#edit-product").dateRange({
                'dateFormat':'yyyy/mm/dd',
                'from': {
                    'id': 'special-from-date'
                },
                'to': {
                    'id': 'special-to-date'
                }
            });
            $('#wk-mp-save-duplicate-btn').click(function () {
                $("#edit-product").append('<input type="hidden" name="back" value="duplicate">');
                $('#save-btn').trigger('click');
            });
            $('#save-btn').click(function (e) {
                if ($("#edit-product").valid()!==false) {
                    if ($('#description_ifr').length) {
                        var desc = $('#description_ifr').contents().find('#tinymce').text();
                        $('#description-error').remove();
                        if (desc === "" || desc === null) {
                            $('#description-error').remove();
                            $('#description').parent().append('<div class="mage-error" generated="true" id="description-error">This is a required field.</div>');
                        }
                        if (desc !== "" && desc !== null) {
                            $('.button').css('opacity','0.7');
                            $('.button').css('cursor','default');
                            $('.button').attr('disabled','disabled');
                            $('body').trigger('processStart');
                            $('#edit-product').submit();
                        } else {
                            return false;
                        }
                    }
                }
            });
            $('#edit-product').on('submit', function(e){
                if (Number(self.options.isAdminViewCategoryTree)) {
                    var category = $('.values-category').map((_,el) => el.value).get();
                    $('#category-error').remove();
                    if (!category.length) {
                        $(".loading-mask").remove();
                        $("#list-category").append('<div id="category-error" style="display: block;color: #e02b27">'+messageRequired+'</div>');
                        $("#save-btn").removeAttr("disabled");
                        $("#save-btn").css("opacity","1");
                        $("#wk-mp-save-duplicate-btn").removeAttr("disabled");
                        $("#wk-mp-save-duplicate-btn").css("opacity","1");
                        return false;
                    }else {
                        $('#category-error').remove();
                    }
                }
            });
            $('.input-text').change(function () {
                var validt = $(this).val();
                var regex = /(<([^>]+)>)/ig;
                var mainvald = validt .replace(regex, "");
                $(this).val(mainvald);
            });
            $('input#sku').change(function () {
                var sku=$('input#sku').val();
                var sku_length=sku.length;
                if (sku_length === 0) {
                    alert({
                        content: self.options.errorMessageSku
                    });
                    $('div#skuavail').css('display','none');
                    $('div#skunotavail').css('display','none');
                } else {
                    self.callVerifySkuAjaxFunction();
                }
            });
            $('body').on('change','.wk-elements',function () {
                var category_id=$(this).val();
                if (this.checked === true) {
                    var $obj = $('<input/>').attr('type','hidden').attr('name','product[category_ids][]').attr('id','wk-cat-hide'+category_id).attr('value',category_id);
                    $('.wk-for-validation').append($obj);
                } else {
                    $('#wk-cat-hide'+category_id).remove();
                }
            });
            $("#wk-bodymain").delegate('.wk-plus ,.wk-plusend,.wk-minus, .wk-minusend ',"click",function () {
                var thisthis=$(this);
                if (thisthis.hasClass("wk-plus") || thisthis.hasClass("wk-plusend")) {
                    if (thisthis.hasClass("wk-plus")) {
                        thisthis.removeClass('wk-plus').addClass('wk-plus_click');
                    }
                    if (thisthis.hasClass("wk-plusend")) {
                        thisthis.removeClass('wk-plusend').addClass('wk-plusend_click');
                    }
                    thisthis.prepend("<span class='wk-node-loader'></span>");
                    self.callCategoryTreeAjaxFunction(thisthis);
                }
                if (thisthis.hasClass("wk-minus") || thisthis.hasClass("wk-minusend")) {
                    self.callRemoveCategoryNodeFunction(thisthis);
                }
            });
        },
        callVerifySkuAjaxFunction: function () {
            var self = this;
            $.ajax({
                url: self.options.verifySkuAjaxUrl,
                type: "POST",
                data: {sku:$('input#sku').val(), product_id:self.options.productid},
                dataType: 'html',
                success:function ($data) {
                    $data=JSON.parse($data);
                    if ($data.avialability==1) {
                        $('div#skuavail').css('display','block');
                        $('div#skunotavail').css('display','none');
                    } else {
                        $('div#skunotavail').css('display','block');
                        $('div#skuavail').css('display','none');
                        $("input#sku").attr('value','');
                    }
                },
                error: function (response) {
                    alert({
                        content: self.options.ajaxErrorMessage
                    });
                }
            });
        },
        callCategoryTreeAjaxFunction: function (thisthis) {
            var self = this;
            var i, len, name, id;
            $.ajax({
                url     :   self.options.categoryTreeAjaxUrl,
                type    :   "POST",
                data    :   {
                    parentCategoryId:thisthis.siblings("input").val()
                },
                dataType:   "html",
                success :   function (content) {
                    var newdata=  $.parseJSON(content);
                    len = newdata.length;
                    var pxl= parseInt(thisthis.parent(".wk-cat-container").css("margin-left").replace("px",""))+20;
                    thisthis.find(".wk-node-loader").remove();
                    if (thisthis.attr("class") == "wk-plus") {
                        thisthis.attr("class","wk-minus");
                    }
                    if (thisthis.attr("class") == "wk-plusend") {
                        thisthis.attr("class","wk-minusend");
                    }
                    if (thisthis.attr("class") == "wk-plus_click") {
                        thisthis.attr("class","wk-minus");
                    }
                    if (thisthis.attr("class") == "wk-plusend_click") {
                        thisthis.attr("class","wk-minusend");
                    }
                    for (i=0; i<len; i++) {
                        id=newdata[i].id;
                        name=newdata[i].name;
                        if ($('#wk-cat-hide'+id).length) {
                            if (newdata[i].counting === 0) {
                                thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-no"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements required-category" type="checkbox" checked name="product[category_ids][]" value='+ id +'></div>');
                            } else {
                                thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-plusend"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements required-category" type="checkbox" checked name="product[category_ids][]" value='+ id +'></div>');
                            }
                        } else {
                            if (newdata[i].counting === 0) {
                                thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-no"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements required-category" type="checkbox" name="product[category_ids][]" value='+ id +'></div>');
                            } else {
                                thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-plusend"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements required-category" type="checkbox" name="product[category_ids][]" value='+ id +'></div>');
                            }
                        }
                    }
                    thisthis.parent(".wk-cat-container").nextAll().slideDown(300);
                },
                error: function (response) {
                    alert({
                        content: self.options.ajaxErrorMessage
                    });
                }
            });
        },
        callRemoveCategoryNodeFunction: function (thisthis) {
            if (thisthis.attr("class") == "wk-minus") {
                thisthis.attr("class","wk-plus");
            }
            if (thisthis.attr("class") == "wk-minusend") {
                thisthis.attr("class","wk-plusend");
            }
            var thiscategory = thisthis.parent(".wk-cat-container");
            var marg= parseInt(thiscategory.css("margin-left").replace("px",""));
            while (thiscategory.next().hasClass("wk-removable")) {
                if (parseInt(thiscategory.next().css("margin-left").replace("px",""))>marg) {
                    thiscategory.next().slideUp("slow",function () {
                        $(this).remove();
                    });
                }
                thiscategory = thiscategory.next();
                if (typeof thiscategory.next().css("margin-left")!= "undefined") {
                    if (marg == thiscategory.next().css("margin-left").replace("px","")) {
                        break;
                    }
                }
            }
        }
    });
    return $.mage.sellerAddProduct;
});
