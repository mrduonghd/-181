/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */

define(
    [
        'jquery',
        'moment',
    ],
    function ($, moment) {
        'use strict';

        return function () {
            $.validator.addMethod(
                "required-category",
                function(value, element) {
                    return value !== undefined;
                },
                $.mage.__("Please select a category to register the product.")
            );
        }
    }
);
