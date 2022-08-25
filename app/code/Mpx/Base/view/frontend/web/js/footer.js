define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'mage/translate'
], function ($, customerData, urlBuilder, $t) {
    'use strict';

    //Get customer data using Magento customer data session
    //Magentoの顧客データセッションを使用して顧客データを取得する
    var getCustomerInfo = function (href, dataPost) {
        var customer = customerData.get('customer');
        customer.subscribe(function (updatedCustomer) {
            if (updatedCustomer.firstname) {
                $("#footer-login-logout").html(
                    '<a href="' + href + '">' + $t('Logout') +
                    '</a>'
                );
            } else {
                $("#footer-login-logout").html(
                    '<a href="' + href + '">' + $t('Login') + '</a>'
                );
            }
        }, this);
        return customer();
    };

    //Check if a custom is logged in | return bool
    //カスタムがログインしているかどうかを確認します| ブール値を返す
    var isLoggedIn = function (customerInfo, href) {
        customerInfo = customerInfo || getCustomerInfo(href);
        return customerInfo && customerInfo.firstname;
    };

    return function (config) {
        var href = config.href;
        var deferred = $.Deferred();
        var customerInfo = getCustomerInfo(href);
        if (isLoggedIn(customerInfo, href)) {
            //Logged in => show logout link
            //ログイン=>ログアウトリンクを表示
            $("#footer-login-logout").html(
                '<a href="' + href + '">' + $t('Logout') +
                '</a>'
            );
        } else {
            //not Logged in => show login link
            //ログインしていない=>ログインリンクを表示
            deferred.resolve(isLoggedIn(customerInfo));
            $("#footer-login-logout").html(
                '<a href="' + href + '">' + $t('Login') + '</a>'
            );
        }
        return deferred;
    };
});
