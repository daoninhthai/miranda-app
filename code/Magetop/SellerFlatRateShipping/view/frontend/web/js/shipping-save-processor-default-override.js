/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Flat_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
console.log('init Magetop/SellerFlatRateShipping/view/frontend/web/js/shipping-save-processor-default-override.js')
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
        var seller_flat_rate = window.checkoutConfig.seller_flat_rate;         
        return {
            saveShippingInformation: function() {
                var total_shipping_price = 0;
                var string_seller = '';
                if(($('input[id="s_method_sellerflatrate_sellerflatrate"]:checked').length == 1) || ($('input[id="s_method_sellerflatrate"]:checked').length == 1)){
                    $.each(seller_flat_rate, function(index, value) {
                        total_shipping_price += parseInt($("input:radio[name='"+value['input_name']+"']:checked").val());
                        string_seller += value['seller_id']+'-'+$("input:radio[name='"+value['input_name']+"']:checked").attr('price')+',';
                    }); 
                }                        
                console.log('data post multi shipping : '+total_shipping_price+'split'+string_seller);                
                var payload = {

                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code,
                        extension_attributes: {
                            flat_rate_shipping: total_shipping_price+'split'+string_seller
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
