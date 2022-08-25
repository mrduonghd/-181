define([
        'Magento_Checkout/js/view/payment/default',
        'mage/storage',
        'jquery',

    ], function (Component,
                 storage,
                 $) {
        'use strict';
        return {
            componentName: "paypalSdkComponent",
            paypalSdk: window.checkoutConfig.payment.paypaljs.urlSdk,
            onLoadedCallback: '',
            customerId: window.checkoutConfig.payment.paypaljs.customer.id,

            loadSdk: function (callbackOnLoaded) {
                var self = this;
                self.onLoadedCallback = callbackOnLoaded;
                var componentUrl = self.paypalSdk;

                if ((typeof paypal === 'undefined')) {

                    var objCallback = {
                        completeCallback: function (resultIndicator, successIndicator) {
                            self.logger('completeCallback complete');
                        },
                        errorCallback: function () {
                            self.error('Payment errorCallback');
                        },
                        cancelCallback: function () {
                            self.logger('Payment cancelled');
                        },
                        onLoadedCallback: function () {
                            self.logger('PayPal SDK loaded', paypal);
                            $(document).ready(function () {
                                return callbackOnLoaded.call();
                            });
                            self.logger('Load paypal Component');
                        }
                    };

                    window.ErrorCallback = $.proxy(objCallback, "errorCallback");
                    window.CancelCallback = $.proxy(objCallback, "cancelCallback");
                    window.CompletedCallback = $.proxy(objCallback, "completeCallback");

                    requirejs.load({
                        contextName: '_',
                        onScriptLoad: $.proxy(objCallback, "onLoadedCallback"),
                        config: {
                            baseUrl: componentUrl
                        }
                    }, self.componentName, componentUrl);

                    var htmlElement = $('[data-requiremodule="' + self.componentName + '"]')[0];

                    htmlElement.setAttribute('data-error', 'window.ErrorCallback');
                    htmlElement.setAttribute('data-cancel', 'window.ErrorCallback');
                    htmlElement.setAttribute('data-complete', 'window.CompletedCallback');
                }
            },
            logger: function (message, obj) {
                if (window.checkoutConfig.payment.paypaljs.debug) {
                    console.log(message, obj);
                }
            }
        };
    }
);
