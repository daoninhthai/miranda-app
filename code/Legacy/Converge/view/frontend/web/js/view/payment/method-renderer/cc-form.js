define([
  "Magento_Payment/js/view/payment/cc-form",
  "jquery",
  "Magento_Payment/js/model/credit-card-validation/validator",
], function (Component, $) {
  "use strict";

  return Component.extend({
    defaults: {
      template: "Legacy_Converge/payment/cc-form",
      code: "legacy_converge",
      additional_data: {},
    },

    initialize: function () {
      this._super();
      console.log("Legacy_Converge initialized with code:", this.code);
    },

    getCode: function () {
      console.log("getCodasdsade called: ", this.code);
      return this.code;
    },

    isActive: function () {
      const isActive = this.getCode() === this.isChecked();
      console.log("isActive called. Active:", isActive);
      return isActive;
    },

    getData: function () {
      const additionalData = {
        cc_number: this.creditCardNumber(),
        cc_type: this.creditCardType(),
        cc_exp_month: this.creditCardExpMonth(),
        cc_exp_year: this.creditCardExpYear(),
        cc_cid: this.creditCardVerificationNumber(),
      };

      console.log("getData called. Additional Data:", additionalData);

      return {
        method: this.getCode(),
        additional_data: additionalData,
      };
    },

    getSelector: function (field) {
      const selector = "#" + this.getCode() + "_" + field;
      console.log("getSelector called. Selector:", selector);
      return selector;
    },

    validate: function () {
      const selector = this.getSelector("payment_form");
      const form = $(selector);

      if (!form.length) {
        console.error("Form not found for selector:", selector);
        return false;
      }

      // Initialize validation
      form.validation();

      const isValid = form.valid();
      if (!isValid) {
        console.error("Form validation failed.");
      } else {
        console.log("Form validation successful.");
      }
      return isValid;
    },
  });
});
