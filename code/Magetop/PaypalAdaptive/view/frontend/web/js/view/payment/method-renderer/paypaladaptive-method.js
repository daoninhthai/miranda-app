/*browser:true*/
/*global define*/
define(
[
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/payment/additional-validators',
    'mage/url',
],
function (
    $,
    Component,
    placeOrderAction,
    selectPaymentMethodAction,
    customer,
    checkoutData,
    additionalValidators,
    url) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magetop_PaypalAdaptive/payment/paypaladaptive'
        },

        placeOrder: function (data, event) {
            if (event) {
                event.preventDefault();
            }
            var self = this,
                placeOrder,
                emailValidationResult = customer.isLoggedIn(),
                loginFormSelector = 'form[data-role=email-with-possible-login]';
            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
            }
            if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);
                placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                $.when(placeOrder).fail(function () {
                    self.isPlaceOrderActionAllowed(true);
                }).done(this.afterPlaceOrder.bind(this));
                return true;
            }
            return false;
        },

        selectPaymentMethod: function() {
            selectPaymentMethodAction(this.getData());
            checkoutData.setSelectedPaymentMethod(this.item.method);
            return true;
        },

        afterPlaceOrder: function () {
            window.location.replace(url.build('paypaladaptive?act=redirect'));
        },
        
        getInstructions: function () {
            console.log("try to find instructions:"+this.item.method);
            //return window.checkoutConfig.payment.instructions[this.item.method];
        }
    });
}
);