/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
"jquery",
'Magento_Ui/js/modal/alert',
"jquery/ui",
], function ($, alert) {
    'use strict';
    $.widget('mpshipping.mpshippingset', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var sellerWarning = self.options.sellerWarning;
                var shippingType = $('#shipping_type').val();
                if (shippingType == 'free') {
                  $("#price").removeClass('required-entry');
                  $("#price").parents(".field-price").hide();
                }
                $('#shipping_type').on('change',function () {
                  var shipping_type = $(this).val();
                  if (shipping_type == "free") {
                    $("#price").removeClass('required-entry');
                    $("#price").parents(".field-price").hide();
                  } else {
                    $("#price").addClass('required-entry');
                    $("#price").parents(".field-price").show();
                  }

                });
            });
        }
    });
    return $.mpshipping.mpshippingset;
});
