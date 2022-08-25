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
    $.widget('mpshipping.mpshipping', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var sellerWarning = self.options.sellerWarning;
                $('#is_range').on('change',function () {
                  var isRange = $(this).val();
                  if (isRange == "yes") {
                    $("#zipcode").removeClass('required-entry');
                    $("#zipcode").parents(".field-zipcode").removeClass("_required");
                    $("#zipcode").parents(".field-zipcode").removeClass("required");
                  } else {
                    $("#price").addClass('required-entry');
                    $("#zipcode").parents(".field-zipcode").addClass("_required");
                    $("#zipcode").parents(".field-zipcode").addClass("required");
                  }

                });
            });
        }
    });
    return $.mpshipping.mpshipping;
});
