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
                "validate-length",
                function(value, element) {
                    return value.length <= 32;
                },
                $.mage.__("Please enter less or equal than 32 symbols.")
            );
        }
    }
);
