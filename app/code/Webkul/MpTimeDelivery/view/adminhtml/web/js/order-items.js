/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDelivery
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
    "jquery",
    "jquery/ui",
    "mage/translate"
    ],
    function ($) {
        'use strict';
        $.widget(
            'mage.orderItems',
            {
                options: {},
                _create: function () {
                    var self = this;
                    $(document).ready(
                        function () {
                            var info = self.options.info;
                            $(".edit-order-table tbody").each(
                                function () {
                                    if ($(this).find('div[id^=order_item_]').length) {
                                        var itemId = $(this).find('div[id^=order_item_]').attr('id').replace('order_item_', '');
                                        if (itemId != '' || $itemId !== 'undefined') {
                                            if (info[itemId]['date'] != '' && info[itemId]['time'] != '') {
                                                $(this).find("tr td:first-child").append("<span class='order-status'><p><strong>"+$.mage.__("Delivery Date/Day: ")+"</strong>"+info[itemId]['date']+"</p><p><strong>"+$.mage.__("Delivery Time: ")+"</strong>"+info[itemId]['time']+"</p></span>");
                                            }
                                        }
                                    }
                                }
                            );
                        }
                    )
                }
            }
        );
        return $.mage.orderItems;
    }
);