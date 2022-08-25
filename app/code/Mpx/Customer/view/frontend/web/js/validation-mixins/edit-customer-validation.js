/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Customer
 * @author    Mpx
 */

define(['jquery'], function($) {
    'use strict';

    return function () {
        $.validator.addMethod(
            'validate-shop-title',
            function (value,element) {
                return value.trim() !== '';
            }, $.mage.__('Please enter the shop name'));

    }
})
