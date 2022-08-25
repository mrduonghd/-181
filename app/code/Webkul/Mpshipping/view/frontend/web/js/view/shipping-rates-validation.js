/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Webkul_Mpshipping/js/model/shipping-rates-validator',
        'Webkul_Mpshipping/js/model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        webkulshippingRatesValidator,
        webkulshippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('webkulshipping', webkulshippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('webkulshipping', webkulshippingRatesValidationRules);

        return Component;
    }
);
