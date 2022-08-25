/**
* Webkul Software
*
* @category Webkul
* @package Webkul_MpTimeDelivery
* @author Webkul
* @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
* @license https://store.webkul.com/license.html
*/
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Webkul_MpTimeDelivery/js/view/shipping': true
            },
            'Webkul_OneStepCheckout/js/view/shipping': {
                'Webkul_MpTimeDelivery/js/view/shipping':true
            },
            'Magento_Checkout/js/view/payment/default': {
                'Webkul_MpTimeDelivery/js/view/payment/default': true
            }
        }
    },
    "map": {
        "*": {
            'Magento_Checkout/js/model/shipping-save-processor/default': 'Webkul_MpTimeDelivery/js/model/shipping-save-processor/default',
        }
    }
};