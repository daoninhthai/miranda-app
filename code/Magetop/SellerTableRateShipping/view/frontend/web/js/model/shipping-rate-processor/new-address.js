/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Table_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 /*global define*/
define(
    [
        'jquery',    
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/error-processor'
    ],
    function ($, resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor) {
        'use strict';

        return {
            /**
             * Get shipping rates for specified address.
             * @param {Object} address
             */
            getRates: function (address) {
                $('#marketpkace-table-rate-shipping .block-title-seller').html('');
                var seller_table_rate = window.checkoutConfig.seller_table_rate; 
                console.log(seller_table_rate);
                console.log(address);
                $.each(seller_table_rate, function(index, value) {
                    $('#marketpkace-table-rate-shipping .block-title-seller').append(
                        '<table>'+
                        	'<thead>'+
                                '<tr>'+
                            		'<th colspan="3">'+value.seller_name+'</th>'+
                        		'</tr>'+
                        	'</thead>'+
                        	'<tbody>'+
                            '</tbody>'+
                        '</table>'
                    );
                    $.each(value.detail, function(index, vl) {
                        if((address.countryId == vl.country_code)|| (vl.country_code == '*')){
                            if((address.regionId == vl.region_id) || (address.region == vl.region_id) || (vl.region_id == '*')){
                                if(((parseInt(address.postcode) >= parseInt(vl.zip_from)) && (parseInt(address.postcode) <= parseInt(vl.zip_to))) || ((vl.zip_from == '*') && (vl.zip_to == '*'))){
                                    $('#marketpkace-table-rate-shipping .block-title-seller table:last tbody').append(
                                        '<tr>'+
                                            '<td><input checked="checked" type="radio" id="'+vl.id+'" name="'+vl.name+'" value="'+vl.value+'" price="'+vl.price+'" /></td>'+
                                            '<td><label style="cursor: pointer;" for="'+vl.id+'">'+vl.title+'</label></td>'+
                                            '<td><label style="cursor: pointer;" for="'+vl.id+'">'+vl.price+'</label></td>'+
                                        '</tr>'
                                    );
                                    //return false;
                                }
                            }
                        }
                    });
                    if($('#marketpkace-table-rate-shipping .block-title-seller table:last tbody').is(':empty')){
                        $('#marketpkace-table-rate-shipping .block-title-seller table:last tbody').append("<tr><td style='color:red'>Don't have shipment solution for this combination</tr></td>");
                    }                     
                });  
                
                var check_radio;
                if(($('input[id="s_method_sellertablerate_sellertablerate"]:checked').length == 1) || ($('input[id="s_method_sellertablerate"]:checked').length == 1)){
                    $.each(seller_table_rate, function(index, value) {
                        if($("input:radio[name='"+value['input_name']+"']").is(":checked")) {
                            
                        }else{
                            check_radio = 'notok';
                        }
                    }); 
                }        
                if(check_radio == 'notok'){      
                    $('.magetop_sellertablerateshipping_button').hide();
                }else{
                    $('.magetop_sellertablerateshipping_button').show();
                }   
                console.log('magetop_sellertablerateshipping_button');                                                                               
                
                shippingService.isLoading(true);
                var cache = rateRegistry.get(address.getCacheKey()),
                    serviceUrl = resourceUrlManager.getUrlForEstimationShippingMethodsForNewAddress(quote),
                    payload = JSON.stringify({
                            address: {
                                'street': address.street,
                                'city': address.city,
                                'region_id': address.regionId,
                                'region': address.region,
                                'country_id': address.countryId,
                                'postcode': address.postcode,
                                'email': address.email,
                                'customer_id': address.customerId,
                                'firstname': address.firstname,
                                'lastname': address.lastname,
                                'middlename': address.middlename,
                                'prefix': address.prefix,
                                'suffix': address.suffix,
                                'vat_id': address.vatId,
                                'company': address.company,
                                'telephone': address.telephone,
                                'fax': address.fax,
                                'custom_attributes': address.customAttributes,
                                'save_in_address_book': address.saveInAddressBook
                            }
                        }
                    );

                if (cache) {
                    shippingService.setShippingRates(cache);
                    shippingService.isLoading(false);
                } else {
                    storage.post(
                        serviceUrl, payload, false
                    ).done(
                        function (result) {
                            rateRegistry.set(address.getCacheKey(), result);
                            shippingService.setShippingRates(result);
                        }
                    ).fail(
                        function (response) {
                            shippingService.setShippingRates([]);
                            errorProcessor.process(response);
                        }
                    ).always(
                        function () {
                            shippingService.isLoading(false);
                        }
                    );
                }
            }
        };
    }
);
