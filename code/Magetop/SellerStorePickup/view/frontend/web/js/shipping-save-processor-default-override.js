/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
console.log('init Magetop/SellerStorePickup/view/frontend/web/js/shipping-save-processor-default-override.js')
define(
    [
        'jquery',    
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor'
    ],
    function ($, ko, quote, resourceUrlManager, storage, paymentService, methodConverter, errorProcessor) {
        'use strict';
        var seller_store_pickup = window.checkoutConfig.seller_store_pickup;         
        return {
            saveShippingInformation: function() {
                var total_shipping_price = 0;
                var string_seller = '';
                if($('input[id="s_method_sellerstorepickup_sellerstorepickup"]:checked').length == 1){
                    $.each(seller_store_pickup, function(index, value) {
                        total_shipping_price += parseInt($("select[name='"+value['input_name']+"']").val());
                        string_seller += value['seller_id']+'-'+$("select[name='"+value['input_name']+"']").find(":selected").attr('price')+'-'+$("select[name='"+value['input_name']+"']").find(":selected").attr('store_id')+',';
                    }); 
                }                          
                console.log('data post store pickup shipping : '+total_shipping_price+'split'+string_seller);                
                var payload = {

                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code,
                        extension_attributes: {
                            store_pickup: total_shipping_price+'split'+string_seller
                        }
                    }
                };

                return storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        quote.setTotals(response.totals);
                        paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                    }
                );
            }
        };
    }
);
