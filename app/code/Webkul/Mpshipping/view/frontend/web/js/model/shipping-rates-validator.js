/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*global define*/
define(
    [
        'jquery',
        'mageUtils',
        'Webkul_Mpshipping/js/model/shipping-rates-validation-rules',
        'mage/translate'
    ],
    function ($, utils, validationRules, $t) {
        'use strict';
        return {
            validationErrors: [],
            validate: function (address) {
                var self = this;

                this.validationErrors = [];
                $.each(validationRules.getRules(), function (field, rule) {
                    if (rule.required && utils.isEmpty(address[field])) {
                        var message = $t('Field ') + field + $t(' is required.');

                        self.validationErrors.push(message);
                    }
                });

                return !Boolean(this.validationErrors.length);
            }
        };
    }
);
