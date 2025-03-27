/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_DeliveryTime
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';
    
    var seller_flat_rate = window.checkoutConfig.seller_flat_rate;
    var seller_table_rate = window.checkoutConfig.seller_table_rate;
    var seller_store_pickup = window.checkoutConfig.seller_store_pickup;
            
    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();

            if (!shippingAddress.hasOwnProperty('extension_attributes')) {
                shippingAddress.extension_attributes = {};
            }
            
            var total_flat_rate_price = 0;
            var string_flat_rate = '';
            if($('input[checked="sellerflatrate_sellerflatrate"]:checked').length == 1){
                $.each(seller_flat_rate, function(index, value){
                    total_flat_rate_price += parseInt($("#marketplace_sellerflatrate input:radio[name='"+value['input_name']+"']:checked").val());
                    string_flat_rate += value['seller_id']+'-'+$("#marketplace_sellerflatrate input:radio[name='"+value['input_name']+"']:checked").attr('price')+',';
                });
            }
            
            var total_table_rate_price = 0;
            var string_table_rate = '';
            if($('input[checked="sellertablerate_sellertablerate"]:checked').length == 1){
                $.each(seller_table_rate, function(index, value){
                    total_table_rate_price += parseInt($("#marketplace_sellertablerate input:radio[name='"+value['input_name']+"']:checked").val());
                    string_table_rate += value['seller_id']+'-'+$("#marketplace_sellertablerate input:radio[name='"+value['input_name']+"']:checked").attr('price')+',';
                });
            }
            
            var total_store_pickup_price = 0;
            var string_store_pickup = '';
            if($('input[checked="sellerstorepickup_sellerstorepickup"]:checked').length == 1){
                $.each(seller_store_pickup, function(index, value){
                    total_store_pickup_price += parseInt($("#marketplace_sellerstorepickup select[name='"+value['input_name']+"']").val());
                    string_store_pickup += value['seller_id']+'-'+$("#marketplace_sellerstorepickup select[name='"+value['input_name']+"']").find(":selected").attr('price')+'-'+$("#marketplace_sellerstorepickup select[name='"+value['input_name']+"']").find(":selected").attr('store_id')+',';
                });
            }
            
            var shippingData = {
                seller_flat_rate_shipping: total_flat_rate_price+'split'+string_flat_rate,
                seller_table_rate_shipping: total_table_rate_price+'split'+string_table_rate,
                seller_store_pickup_shipping: total_store_pickup_price+'split'+string_store_pickup
            };
            
            shippingAddress.extension_attributes = $.extend(
                shippingAddress.extension_attributes,
                shippingData
            );
            
            return originalAction();
        });
    };
});