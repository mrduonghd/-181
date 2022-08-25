/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */

var config = {
    config: {
        mixins: {
            'mage/validation': {
                'Mpx_Marketplace/js/validation-mixins/japan-date-validation': true,
                'Mpx_Marketplace/js/validation-mixins/sku-validation': true,
                'Mpx_Marketplace/js/validation-mixins/category-validation': true
            },
            'Webkul_Marketplace/js/order/shipment': {
                'Mpx_Marketplace/js/order/shipment': true
            },
        }
    },
    map: {
        '*': {
            sellerOrderShipment: 'Mpx_Marketplace/js/order/shipment',
        }
    },
}
