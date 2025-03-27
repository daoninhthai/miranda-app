define([
    "uiComponent",
    "Magento_Checkout/js/model/payment/renderer-list",
], function (Component, rendererList) {
    "use strict";

    rendererList.push({
        type: "legacy_converge",
        component: "Legacy_Converge/js/view/payment/method-renderer/cc-form",
    });

    return Component.extend({});
});
