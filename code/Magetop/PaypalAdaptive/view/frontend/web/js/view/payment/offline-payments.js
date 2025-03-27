define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paypaladaptive',
                component: 'Magetop_PaypalAdaptive/js/view/payment/method-renderer/paypaladaptive-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);