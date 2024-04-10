/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
var config = {
    map: {
        '*': {
            "Magento_Checkout/js/view/shipping" : "Magetop_SellerShipping/js/view/shipping",
            "Magento_Checkout/js/model/shipping-rate-processor/new-address" : "Magetop_SellerShipping/js/model/shipping-rate-processor/new-address",
            "Magento_Checkout/js/model/shipping-rate-processor/customer-address" : "Magetop_SellerShipping/js/model/shipping-rate-processor/customer-address",
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Magetop_SellerShipping/js/action/set-shipping-information-mixin': true
            }
        }
    } 
};