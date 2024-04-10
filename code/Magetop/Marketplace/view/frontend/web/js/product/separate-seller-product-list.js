/**
 * Magetop Development
 *
 * @category  Magetop Extension
 * @package   Magetop_Marketplace
 * @author    Magetop
 * @copyright Copyright (c) Magetop (https://www.magetop.com)
 * @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'uiComponent',
    'mage/translate',
    'Magento_Ui/js/modal/confirm'
], function ($, Component, $t, confirm) {
    'use strict';
    return Component.extend({
        initialize: function () {
            window.FORM_KEY = $("input[name=form_key]").val();
            this._super();
            var self = this;
            $("body").on("click", ".mp-edit", function () {
                var $url = $(this).attr('data-url');
                confirm({
                    content: $t(" Are you sure you want to edit this product ? "),
                    actions: {
                        confirm: function () {
                            window.location = $url;
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            });
            $("body").on("click", ".mp-delete", function () {
                var $url = $(this).attr('data-url');
                confirm({
                    content: $t(" Are you sure you want to delete this product ? "),
                    actions: {
                        confirm: function () {
                            window.location = $url;
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            });
        }
    });
});
