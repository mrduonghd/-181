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
                'validate-japanese-date',
                function (value, element) {
                    if (!value) {
                        return true;
                    }
                    // YYYY/MM/DD stand for Japanese locale time format
                    return moment(value, 'YYYY/MM/DD', true).isValid();
                },
                $.mage.__('The date entered is incorrect.')
            )
        }
    }
);
