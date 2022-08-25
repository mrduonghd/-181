define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'paypalSdk',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'ko',
        'mage/translate',
        'mage/storage',
        'Magento_Customer/js/customer-data',
        'uiLayout',

    ],
    function (Component,
              $,
              paypalSdk,
              selectPaymentMethodAction,
              checkoutData,
              quote,
              ko,
              $t,
              storage,
              customerData,
              layout) {
        'use strict';

        window.checkoutConfig.payment.paypaljs.template = 'Mpx_PaypalJs/payment/paypal-js';

        return Component.extend({
            defaults: {
                template: window.checkoutConfig.payment.paypaljs.template
            },

            paypalMethod: 'paypaljs',
            orderId: null,
            additionalData: null,
            paypalConfigs: window.checkoutConfig.payment.paypaljs,
            isFormValid: ko.observable(false),
            isSelected: function () {
                var self = this;

                if (quote.paymentMethod() && (quote.paymentMethod().method == self.paypalMethod)) {
                    return self.selectedMethod;
                }

                return false;
            },
            selectedPayPalMethod: function (method) {
                var self = this;
                var data = this.getData();

                self.messageContainer.clear();
                self.selectedMethod = method;
                self.paypalMethod = self.selectedMethod;
                data.method = self.paypalMethod;

                selectPaymentMethodAction(data);
                checkoutData.setSelectedPaymentMethod(this.item.method);
            },

            getTitleMethodPaypal: function () {
                return this.paypalConfigs.title;
            },
            getCode: function (method) {

                return method;
            },
            getData: function () {
                var self = this;

                var data = {
                    method: self.paypalMethod,
                    additional_data: self.additionalData,
                };

                return data;
            },
            grandTotal: function () {
                /** @type {Object} */
                var totals = quote.getTotals()();
                return (totals ? totals : quote)['base_grand_total'];
            },
            itemTotal: function () {
                var self = this;
                var grandTotal = self.grandTotal();
                var shipping = quote.getTotals()()['shipping_incl_tax'];
                var discount = quote.getTotals()()['discount_amount'];
                var tax_amount = quote.getTotals()()['tax_amount'];
                var shipping_discount = quote.getTotals()()['shipping_discount_amount'];
                return  grandTotal - shipping + discount - tax_amount + shipping_discount;
            },
            renderButton: function (fundingSource, elementId) {
                var self = this;
                var button = paypal.Buttons({
                    fundingSource: fundingSource,
                    createOrder: function (data, actions) {
                        return actions.order.create({
                            application_context: {
                                shipping_preferences: 'SET_PROVIDED_ADDRESS',
                            },
                            intent: self.paypalConfigs.intent,
                            payer: {
                                email_address: quote.guestEmail,
                                name: {
                                    given_name: quote.billingAddress().firstname,
                                    surname: quote.billingAddress().lastname
                                },
                                phone: {
                                    phone_number: {
                                        national_number: quote.billingAddress().telephone
                                    }
                                },
                                address: {
                                    address_line_1: quote.billingAddress().street[0],
                                    address_line_2: quote.billingAddress().street[1],
                                    admin_area_2: quote.billingAddress().city,
                                    postal_code: quote.billingAddress().postcode,
                                    country_code: quote.billingAddress().countryId,
                                },
                            },
                            purchase_units: [{
                                amount: {
                                    value: self.grandTotal(),
                                    breakdown: {
                                        item_total:{
                                            value: self.itemTotal(),
                                            currency_code: self.paypalConfigs.currency,
                                        },
                                        shipping: {
                                            value: quote.getTotals()()['shipping_incl_tax'],
                                            currency_code: self.paypalConfigs.currency,
                                        },
                                        discount: {
                                            value: quote.getTotals()()['discount_amount'],
                                            currency_code: self.paypalConfigs.currency,
                                        },
                                        tax_total: {
                                            value: quote.getTotals()()['tax_amount'],
                                            currency_code: self.paypalConfigs.currency,
                                        },
                                        handling: {
                                            value: 0,
                                            currency_code: self.paypalConfigs.currency,
                                        },
                                        insurance: {
                                            value: 0,
                                            currency_code: self.paypalConfigs.currency,
                                        },
                                        shipping_discount:{
                                            value: quote.getTotals()()['shipping_discount_amount'],
                                            currency_code: self.paypalConfigs.currency,
                                        }
                                    },
                                },
                                invoice_id:self.paypalConfigs.invoice_id
                            }]
                        });
                    },
                    onApprove: function (data, actions) {
                        if (self.paypalConfigs.intent === 'authorize') {
                            return actions.order.authorize().then(function (res) {
                                self.orderId = data.orderID;
                                self.additionalData = self.setAdditionalData(res);
                                self.placeOrder();
                            });
                        } else {
                            return actions.order.capture().then(function (res) {
                                self.orderId = data.orderID;
                                self.additionalData = self.setAdditionalData(res);
                                self.placeOrder();
                            });
                        }
                    },
                    onError: function (err) {
                        self.logger('paypal_js#hostedfieldsRender#onError', err);
                        self.messageContainer.addErrorMessage({
                            message: $t('Transaction cannot be processed, please verify your card information or try another.')
                        });
                    },


                });

                if (button.isEligible()) {
                    button.render('#' + elementId);
                }
            },
            setAdditionalData: function (res) {
                if ( res.intent === 'AUTHORIZE'  ){
                    return {
                        create_time: res.create_time,
                        order_id: res.id,
                        invoice_id: res.purchase_units[0].invoice_id,
                        status: res.status,
                        intent: res.intent,
                        authorization_id:  res.purchase_units[0].payments.authorizations[0].id,
                        settlement_amount:  self.grandTotal()
                    };
                }else {
                    return {
                        create_time: res.create_time,
                        order_id: res.id,
                        invoice_id: res.purchase_units[0].invoice_id,
                        status: res.status,
                        intent: res.intent,
                        captured_id: res.purchase_units[0].payments.captures[0].id,
                        settlement_amount: self.grandTotal()
                    };
                }
            },
            loadSdk: function () {
                var self = this;
                self.logger('loadSDK');

                if ((typeof paypal === 'undefined')) {
                    var body = $('body').loader();
                    self.logger('Paypal JS SDK not loaded');
                    body.loader('show');

                    return paypalSdk.loadSdk(function () {
                        self.renderButtons();
                        body.loader('hide');

                        return this;
                    });
                }
            },
            renderButtons: function () {
                var self = this;

                var FUNDING_SOURCES = {
                    [paypal.FUNDING.PAYPAL]: 'paypal-button-container',
                    [paypal.FUNDING.CARD]: 'card-button-container',
                };
                // Loop over each funding source / payment method
                Object.keys(FUNDING_SOURCES).forEach(function (fundingSource) {
                    console.log('completeRender#fundingSource', fundingSource);

                    self.renderButton(fundingSource, FUNDING_SOURCES[fundingSource])
                });

            },
            initializeEvents: function () {
                var self = this;
                var body = $('body').loader();
                self.loadSdk();
                $('#paypalcp_spb').change(function () {
                    if (this.checked) {
                        self.loadSdk();
                    }
                });
            },
            _enableCheckout: function () {
                $('#submit').prop('disabled', false);

                var body = $('body').loader();
                body.loader('hide');
            },
            completeRender: function () {
                var self = this;

                $('.ppjs.payment-method').removeClass('_active');
                self.initializeEvents();
                self._enableCheckout();

            },
            logger: function (message, obj) {
                if (window.checkoutConfig.payment.paypaljs.debug) {
                    console.log(message, obj);
                }
            },
            activeCard: function (){
                var self = this;
                var enable_card = self.paypalConfigs.activeCard;
                if (enable_card === '1') {
                    return true;
                }
                 return false;
            },
            getCreditCardTitle :function (){
                self = this;
                return self.paypalConfigs.credit_card_title;
            },

            createMessagesComponent: function () {

                var messagesComponent = {
                    parent: this.name,
                    name: this.name + '.messages',
                    displayArea: 'messages',
                    component: 'Mpx_PaypalJs/js/view/messages',
                    config: {
                        messageContainer: this.messageContainer
                    }
                };

                layout([messagesComponent]);

                return this;
            },
        });
    }
);
