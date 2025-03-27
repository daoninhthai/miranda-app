/*jshint browser:true jquery:true expr:true*/
define([
    'jquery',
    'Magetop_Marketplace/catalog/type-events',
    'Magetop_Marketplace/js/product/weight-handler'
], function ($, productType, weight) {
    'use strict';

    return {

        /**
         * Constructor component
         */
        'Magetop_Marketplace/js/bundle-type-handler': function () {
            this.bindAll();
            this._initType();
        },

        /**
         * Bind all
         */
        bindAll: function () {
            $(document).on('changeTypeProduct', this._initType.bind(this));
        },

        /**
         * Init type
         * @private
         */
        _initType: function () {
            if (
                productType.type.real === 'bundle' &&
                productType.type.current !== 'bundle' &&
                !weight.isLocked()
            ) {
                weight.switchWeight();
            }
        }
    };
});
