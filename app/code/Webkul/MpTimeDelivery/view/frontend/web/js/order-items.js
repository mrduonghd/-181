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
                            $("#my-orders-table tbody").each(
                                function () {
                                    self = this;
                                    $(this).find('tr[id^=order-item-row-]').each(function(){
                                        let itemId = $(this).attr('id').replace('order-item-row-', '');
                                        if (itemId != '' || $itemId !== 'undefined') {

                                            if (info[itemId]['date'] != '' && info[itemId]['time'] != '') {
                                                $(self).find("tr[id^=order-item-row-"+itemId+ "] td:first-child").append("<span class='order-status'><p><strong>"+$.mage.__("Delivery Date/Day: ")+"</strong><br>"+info[itemId]['date']+"</p><p><strong>"+$.mage.__("Delivery Time: ")+"</strong><br>"+info[itemId]['time']+"</p></span>");
                                            }
                                        }
                                    });
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