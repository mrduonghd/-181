define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'paypaljs',
                component: 'Mpx_PaypalJs/js/view/payment/method-renderer/paypal-js'
            }
        );
        return Component.extend({});
    }
);
