/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'mage/translate',
    'Magento_Checkout/js/model/shipping-rate-service'
], function (
    $,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    createShippingAddress,
    selectShippingAddress,
    shippingRatesValidator,
    formPopUpState,
    shippingService,
    selectShippingMethodAction,
    rateRegistry,
    setShippingInformationAction,
    stepNavigator,
    modal,
    checkoutDataResolver,
    checkoutData,
    registry,
    $t
) {
    'use strict';

    var popUp = null;
    var seller_flat_rate = window.checkoutConfig.seller_flat_rate;
    var seller_table_rate = window.checkoutConfig.seller_table_rate;
    var seller_store_pickup = window.checkoutConfig.seller_store_pickup;      

    return Component.extend({
        defaults: {
            template: 'Magetop_SellerShipping/shipping',
            shippingFormTemplate: 'Magento_Checkout/shipping-address/form',
            shippingMethodListTemplate: 'Magetop_SellerShipping/shipping-address/shipping-method-list',
            shippingMethodItemTemplate: 'Magetop_SellerShipping/shipping-address/shipping-method-item',
            imports: {
                countryOptions: '${ $.parentName }.shippingAddress.shipping-address-fieldset.country_id:indexedOptions'
            }
        },
        visible: ko.observable(!quote.isVirtual()),
        errorValidationMessage: ko.observable(false),
        isCustomerLoggedIn: customer.isLoggedIn,
        isFormPopUpVisible: formPopUpState.isVisible,
        isFormInline: addressList().length === 0,
        isNewAddressAdded: ko.observable(false),
        saveInAddressBook: 1,
        quoteIsVirtual: quote.isVirtual(),

        /**
         * @return {exports}
         */
        initialize: function () {
            var self = this,
                hasNewAddress,
                fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';

            this._super();

            if (!quote.isVirtual()) {
                stepNavigator.registerStep(
                    'shipping',
                    '',
                    $t('Shipping'),
                    this.visible, _.bind(this.navigate, this),
                    this.sortOrder
                );
            }
            checkoutDataResolver.resolveShippingAddress();

            hasNewAddress = addressList.some(function (address) {
                return address.getType() == 'new-customer-address'; //eslint-disable-line eqeqeq
            });

            this.isNewAddressAdded(hasNewAddress);

            this.isFormPopUpVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });

            quote.shippingMethod.subscribe(function () {
                self.errorValidationMessage(false);
            });

            registry.async('checkoutProvider')(function (checkoutProvider) {
                var shippingAddressData = checkoutData.getShippingAddressFromData();

                if (shippingAddressData) {
                    checkoutProvider.set(
                        'shippingAddress',
                        $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                    );
                }
                checkoutProvider.on('shippingAddress', function (shippingAddrsData, changes) {
                    var isStreetAddressDeleted, isStreetAddressNotEmpty;

                    /**
                     * In last modifying operation street address was deleted.
                     * @return {Boolean}
                     */
                    isStreetAddressDeleted = function () {
                        var change;

                        if (!changes || changes.length === 0) {
                            return false;
                        }

                        change = changes.pop();

                        if (_.isUndefined(change.value) || _.isUndefined(change.oldValue)) {
                            return false;
                        }

                        if (!change.path.startsWith('shippingAddress.street')) {
                            return false;
                        }

                        return change.value.length === 0 && change.oldValue.length > 0;
                    };

                    isStreetAddressNotEmpty = shippingAddrsData.street && !_.isEmpty(shippingAddrsData.street[0]);

                    if (isStreetAddressNotEmpty || isStreetAddressDeleted()) {
                        checkoutData.setShippingAddressFromData(shippingAddrsData);
                    }
                });
                shippingRatesValidator.initFields(fieldsetName);
            });
            
            this.sellerFlatRate = seller_flat_rate;
            this.sellerTableRate = seller_table_rate;
            this.sellerStorePickup = seller_store_pickup;

            return this;
        },

        /**
         * Navigator change hash handler.
         *
         * @param {Object} step - navigation step
         */
        navigate: function (step) {
            step && step.isVisible(true);
        },

        /**
         * @return {*}
         */
        getPopUp: function () {
            var self = this,
                buttons;

            if (!popUp) {
                buttons = this.popUpForm.options.buttons;
                this.popUpForm.options.buttons = [
                    {
                        text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                        class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                        click: self.saveNewAddress.bind(self)
                    },
                    {
                        text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                        class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',

                        /** @inheritdoc */
                        click: this.onClosePopUp.bind(this)
                    }
                ];

                /** @inheritdoc */
                this.popUpForm.options.closed = function () {
                    self.isFormPopUpVisible(false);
                };

                this.popUpForm.options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
                this.popUpForm.options.keyEventHandlers = {
                    escapeKey: this.onClosePopUp.bind(this)
                };

                /** @inheritdoc */
                this.popUpForm.options.opened = function () {
                    // Store temporary address for revert action in case when user click cancel action
                    self.temporaryAddress = $.extend(true, {}, checkoutData.getShippingAddressFromData());
                };
                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return popUp;
        },

        /**
         * Revert address and close modal.
         */
        onClosePopUp: function () {
            checkoutData.setShippingAddressFromData($.extend(true, {}, this.temporaryAddress));
            this.getPopUp().closeModal();
        },

        /**
         * Show address form popup
         */
        showFormPopUp: function () {
            this.isFormPopUpVisible(true);
        },

        /**
         * Save new shipping address
         */
        saveNewAddress: function () {
            var addressData,
                newShippingAddress;

            this.source.set('params.invalid', false);
            this.triggerShippingDataValidateEvent();

            if (!this.source.get('params.invalid')) {
                addressData = this.source.get('shippingAddress');
                // if user clicked the checkbox, its value is true or false. Need to convert.
                addressData['save_in_address_book'] = this.saveInAddressBook ? 1 : 0;

                // New address must be selected as a shipping address
                newShippingAddress = createShippingAddress(addressData);
                selectShippingAddress(newShippingAddress);
                checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                checkoutData.setNewCustomerShippingAddress($.extend(true, {}, addressData));
                this.getPopUp().closeModal();
                this.isNewAddressAdded(true);
            }
        },

        /**
         * Shipping Method View
         */
        rates: shippingService.getShippingRates(),
        isLoading: shippingService.isLoading,
        isSelected: ko.computed(function () {
            return quote.shippingMethod() ?
                quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                null;
        }),
        
        /**
         * Magetop Shipping Show Selected
         */
        magetopShipping: function () {
            var shippingSelected = quote.shippingMethod() ? quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code : null;
            if(shippingSelected == 'sellerflatrate_sellerflatrate'){
                $('#marketplace_sellerflatrate').show();
                $('#marketplace_sellerflatrate .seller_flat_rate').show();
            }else{
                $('#marketplace_sellerflatrate').hide();
                $('#marketplace_sellerflatrate .seller_flat_rate').hide();
            }
            if(shippingSelected == 'sellertablerate_sellertablerate'){
                $('#marketplace_sellertablerate').show();
                $('#marketplace_sellertablerate .seller_table_rate').show();
            }else{
                $('#marketplace_sellertablerate').hide();
                $('#marketplace_sellertablerate .seller_table_rate').hide();
            }
            if(shippingSelected == 'sellerstorepickup_sellerstorepickup'){
                $('#marketplace_sellerstorepickup').show();
                $('#marketplace_sellerstorepickup .seller_store_pickup').show();
            }else{
                $('#marketplace_sellerstorepickup').hide();
                $('#marketplace_sellerstorepickup .seller_store_pickup').hide();
            }
            
            $('.select_store_pickup').change(function(){
                var class_detail_store = $('option:selected',this).attr('class_detail_store');
                var class_time_store = $('option:selected',this).attr('class_time_store');
                $('.'+class_detail_store).html('');
                $('.'+class_time_store).html('');
                var value_select = $('option:selected',this).attr('id');
                $.each(seller_store_pickup, function(index, value) {
                    $.each(value.detail, function(index1, value1) {
                        if(value_select == value1.id){
                            $('.'+class_detail_store).html(value1.detail_store);
                            $('.'+class_time_store).html(value1.time_store);
                        }
                    });
                });
            });
            
            setTimeout(function () {
                $('select[name="country_id"],select[name="region_id"],input[name="postcode"]').change(function(){
                    var dataArray = $('#co-shipping-form').serializeArray(),
                    address = {};
                    $(dataArray).each(function(i, field){
                        address[field.name] = field.value;
                    });
                    
                    $('#marketplace_sellertablerate .block-title-seller').html('');
                    var seller_table_rate = window.checkoutConfig.seller_table_rate;
                    console.log(seller_table_rate);
                    console.log(address);
                    $.each(seller_table_rate, function(index, value) {
                        $('#marketplace_sellertablerate .block-title-seller').append(
                            '<h4>'+value.seller_name+'</h4>'+
                            '<table>'+
                            	'<tbody>'+
                                '</tbody>'+
                            '</table>'
                        );
                        $.each(value.detail, function(index, vl) {
                            if((address.country_id == vl.country_code)|| (vl.country_code == '*')){
                                if((address.region_id == vl.region_id) || (vl.region_id == '*')){
                                    if(((parseInt(address.postcode) >= parseInt(vl.zip_from)) && (parseInt(address.postcode) <= parseInt(vl.zip_to))) || ((vl.zip_from == '*') && (vl.zip_to == '*'))){
                                        $('#marketplace_sellertablerate .seller_table_rate .block-title-seller table:last tbody').append(
                                            '<tr>'+
                                                '<td><input checked="checked" type="radio" id="'+vl.id+'" name="'+vl.name+'" value="'+vl.value+'" price="'+vl.price+'" /></td>'+
                                                '<td><label style="cursor: pointer;" for="'+vl.id+'">'+vl.title+'</label></td>'+
                                                '<td><label style="cursor: pointer;" for="'+vl.id+'">'+vl.price+'</label></td>'+
                                            '</tr>'
                                        );
                                    }
                                }
                            }
                        });
                        if($('#marketplace_sellertablerate .seller_table_rate .block-title-seller table:last tbody').is(':empty')){
                            $('#marketplace_sellertablerate .seller_table_rate .block-title-seller table:last tbody').append("<tr><td style='color:red'>Don't have shipment solution for this combination</tr></td>");
                        }
                    });
                });
            }, 2000);
            
            window.checkoutConfig.activeCarriers.forEach(function(item) {
                if(item == 'sellerflatrate'){
                    $('#marketplace_sellerflatrate .seller_table_rate').remove();
                    $('#marketplace_sellerflatrate .seller_store_pickup').remove();
                }else if(item == 'sellertablerate'){
                    $('#marketplace_sellertablerate .seller_flat_rate').remove();
                    $('#marketplace_sellertablerate .seller_store_pickup').remove();
                }else if(item == 'sellerstorepickup'){
                    $('#marketplace_sellerstorepickup .seller_flat_rate').remove();
                    $('#marketplace_sellerstorepickup .seller_table_rate').remove();
                }else{
                    $('#marketplace_'+item).remove();
                }
            });
            
            console.log('selected shipping method : ' + shippingSelected);                        
        },

        /**
         * @param {Object} shippingMethod
         * @return {Boolean}
         */
        selectShippingMethod: function (shippingMethod) {
            if(shippingMethod.carrier_code + '_' + shippingMethod.method_code == 'sellerflatrate_sellerflatrate'){
                $('#marketplace_sellerflatrate').show();
                $('#marketplace_sellerflatrate .seller_flat_rate').show();
            }else{
                $('#marketplace_sellerflatrate').hide();
                $('#marketplace_sellerflatrate .seller_flat_rate').hide();
            }
            if(shippingMethod.carrier_code + '_' + shippingMethod.method_code == 'sellertablerate_sellertablerate'){
                $('#marketplace_sellertablerate').show();
                $('#marketplace_sellertablerate .seller_table_rate').show();
            }else{
                $('#marketplace_sellertablerate').hide();
                $('#marketplace_sellertablerate .seller_table_rate').hide();
            }
            if(shippingMethod.carrier_code + '_' + shippingMethod.method_code == 'sellerstorepickup_sellerstorepickup'){
                $('#marketplace_sellerstorepickup').show();
                $('#marketplace_sellerstorepickup .seller_store_pickup').show();
            }else{
                $('#marketplace_sellerstorepickup').hide();
                $('#marketplace_sellerstorepickup .seller_store_pickup').hide();
            }
            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);
            
            console.log('click shipping method : ' + shippingMethod.carrier_code + '_' + shippingMethod.method_code);                        

            return true;
        },

        /**
         * Set shipping information handler
         */
        setShippingInformation: function () {
            if (this.validateShippingInformation()) {
                quote.billingAddress(null);
                checkoutDataResolver.resolveBillingAddress();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                });
                setShippingInformationAction().done(
                    function () {
                        stepNavigator.next();
                    }
                );
            }
        },

        /**
         * @return {Boolean}
         */
        validateShippingInformation: function () {
            var shippingAddress,
                addressData,
                loginFormSelector = 'form[data-role=email-with-possible-login]',
                emailValidationResult = customer.isLoggedIn(),
                field,
                option = _.isObject(this.countryOptions) && this.countryOptions[quote.shippingAddress().countryId],
                messageContainer = registry.get('checkout.errors').messageContainer;

            if (!quote.shippingMethod()) {
                this.errorValidationMessage(
                    $t('The shipping method is missing. Select the shipping method and try again.')
                );

                return false;
            }else{
                var check_radio;
                if($('input[checked="sellerstorepickup_sellerstorepickup"]:checked').length == 1){
                    $.each(seller_store_pickup, function(index, value) {
                        if($("#marketplace_sellerstorepickup select[name='"+value['input_name']+"']").val() != '') {
                        
                        }else{
                            $("#marketplace_sellerstorepickup select[name='"+value['input_name']+"']").focus();
                            check_radio = 'notok';
                        }
                    });
                }
                if(check_radio == 'notok'){
                    this.errorValidationMessage(
                        $t('Please specify all seller shipping method in Marketplace Seller Store Pickup')
                    );
                    return false;
                }else{
                    console.log('pass store pickup');
                }
                
                if($('input[checked="sellerflatrate_sellerflatrate"]:checked').length == 1){
                    $.each(seller_flat_rate, function(index, value) {
                        if($("#marketplace_sellerflatrate input:radio[name='"+value['input_name']+"']").is(":checked")) {
                            
                        }else{
                            $("#marketplace_sellerflatrate input:radio[name='"+value['input_name']+"']").focus();                            
                            check_radio = 'notok';
                        }
                    });
                }
                if(check_radio == 'notok'){
                    this.errorValidationMessage(
                        $t('Please specify all seller shipping method in Marketplace Seller Flat Rate Shipping')
                    );
                    return false;
                }
            }
            
            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
            }

            if (this.isFormInline) {
                this.source.set('params.invalid', false);
                this.triggerShippingDataValidateEvent();

                if (!quote.shippingMethod()['method_code']) {
                    this.errorValidationMessage(
                        $t('The shipping method is missing. Select the shipping method and try again.')
                    );
                }

                if (emailValidationResult &&
                    this.source.get('params.invalid') ||
                    !quote.shippingMethod()['method_code'] ||
                    !quote.shippingMethod()['carrier_code']
                ) {
                    this.focusInvalid();

                    return false;
                }

                shippingAddress = quote.shippingAddress();
                addressData = addressConverter.formAddressDataToQuoteAddress(
                    this.source.get('shippingAddress')
                );

                //Copy form data to quote shipping address object
                for (field in addressData) {
                    if (addressData.hasOwnProperty(field) &&  //eslint-disable-line max-depth
                        shippingAddress.hasOwnProperty(field) &&
                        typeof addressData[field] != 'function' &&
                        _.isEqual(shippingAddress[field], addressData[field])
                    ) {
                        shippingAddress[field] = addressData[field];
                    } else if (typeof addressData[field] != 'function' &&
                        !_.isEqual(shippingAddress[field], addressData[field])) {
                        shippingAddress = addressData;
                        break;
                    }
                }

                if (customer.isLoggedIn()) {
                    shippingAddress['save_in_address_book'] = 1;
                }
                selectShippingAddress(shippingAddress);
            } else if (customer.isLoggedIn() &&
                option &&
                option['is_region_required'] &&
                !quote.shippingAddress().region
            ) {
                messageContainer.addErrorMessage({
                    message: $t('Please specify a regionId in shipping address.')
                });

                return false;
            }

            if (!emailValidationResult) {
                $(loginFormSelector + ' input[name=username]').trigger('focus');

                return false;
            }

            return true;
        },

        /**
         * Trigger Shipping data Validate Event.
         */
        triggerShippingDataValidateEvent: function () {
            this.source.trigger('shippingAddress.data.validate');

            if (this.source.get('shippingAddress.custom_attributes')) {
                this.source.trigger('shippingAddress.custom_attributes.data.validate');
            }
        }
    });
});
