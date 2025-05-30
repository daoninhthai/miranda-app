define('globalNavigationScroll', [
    'jquery'
], function ($) {
    'use strict';

    var win = $(window),
        subMenuClass = '.submenu',
        fixedClassName = '_fixed',
        menu = $('.menu-wrapper'),
        content = $('.page-wrapper'),
        menuItems = $('#nav').children('li'),
        subMenus = menuItems.children(subMenuClass),
        winHeight,
        menuHeight = menu.height(),
        menuHeightRest = 0,
        menuScrollMax = 0,
        submenuHeight = 0,
        contentHeight,
        winTop = 0,
        winTopLast = 0,
        scrollStep = 0,
        nextTop = 0;

    /**
     * Check if menu is fixed
     * @returns {boolean}
     */
    function isMenuFixed() {
        return (menuHeight < contentHeight) && (contentHeight > winHeight);
    }

    /**
     * Check if class exist than add or do nothing
     * @param {jQuery} el
     * @param $class string
     */
    function checkAddClass(el, $class) {
        if (!el.hasClass($class)) {
            el.addClass($class);
        }
    }

    /**
     * Check if class exist than remove or do nothing
     * @param {jQuery} el
     * @param $class string
     */
    function checkRemoveClass(el, $class) {
        if (el.hasClass($class)) {
            el.removeClass($class);
        }
    }

    /**
     * Calculate and apply menu position
     */
    function positionMenu() {

        //  Spotting positions and heights
        winHeight = win.height();
        contentHeight = content.height();
        winTop = win.scrollTop();
        scrollStep = winTop - winTopLast;
        menuHeightRest = menuHeight - winTop; // is a visible menu height

        if (isMenuFixed()) { // fixed menu cases

            checkAddClass(menu, fixedClassName);

            if (menuHeight > winHeight) { // smart scroll cases

                if (winTop > winTopLast) { //  scroll down

                    menuScrollMax = menuHeight - winHeight;

                    nextTop < (menuScrollMax - scrollStep) ?
                        nextTop += scrollStep : nextTop = menuScrollMax;

                    menu.css('top', -nextTop);

                } else if (winTop <= winTopLast) { // scroll up

                    nextTop > -scrollStep ?
                        nextTop += scrollStep : nextTop = 0;

                    menu.css('top', -nextTop);

                }

            }

        } else { // static menu cases
            checkRemoveClass(menu, fixedClassName);
        }

        //  Save previous window scrollTop
        winTopLast = winTop;

    }

    positionMenu(); // page start calculation

    //  Change position on scroll
    win.on('scroll', function () {
        positionMenu();
    });

    win.on('resize', function () {

        winHeight = win.height();

        //  Reset position if fixed and out of smart scroll
        if ((menuHeight < contentHeight) && (menuHeight <= winHeight)) {
            menu.removeAttr('style');
            menuItems.off();
        }

    });

    //  Add event to menuItems to check submenu overlap
    menuItems.on('click', function (e) {

        var submenu = $(this).children(subMenuClass),
            delta,
            logo = $('.logo')[0].offsetHeight;

        submenuHeight = submenu.height();

        if (submenuHeight > menuHeight && menuHeight + logo > winHeight) {
            menu.height(submenuHeight - logo);
            delta = -menu.position().top;
            window.scrollTo(0, 0);
            positionMenu();
            window.scrollTo(0, delta);
            positionMenu();
            menuHeight = submenuHeight;
        }
    });

});

define('globalNavigation', [
    'jquery',
    'jquery/ui',
    'globalNavigationScroll'
], function ($) {
    'use strict';

    $.widget('mage.globalNavigation', {
        options: {
            selectors: {
                menu: '#nav',
                currentItem: '._current',
                topLevelItem: '.level-0',
                topLevelHref: '> a',
                subMenu: '> .submenu',
                closeSubmenuBtn: '[data-role="close-submenu"]'
            },
            overlayTmpl: '<div class="admin__menu-overlay"></div>'
        },

        _create: function () {
            var selectors = this.options.selectors;

            this.menu = this.element;
            this.menuLinks = $(selectors.topLevelHref, selectors.topLevelItem);
            this.closeActions = $(selectors.closeSubmenuBtn);

            this._initOverlay()
                ._bind();
        },

        _initOverlay: function () {
            this.overlay = $(this.options.overlayTmpl).appendTo('body').hide(0);

            return this;
        },

        _bind: function () {
            var focus = this._focus.bind(this),
                open = this._open.bind(this),
                blur = this._blur.bind(this),
                keyboard = this._keyboard.bind(this);

            this.menuLinks
                .on('focus', focus)
                .on('click', open);

            this.menuLinks.last().on('blur', blur);

            this.closeActions.on('keydown', keyboard);
        },


        /**
         * Remove active class from current menu item
         * Turn back active class to current page menu item
         */
        _blur: function (e) {
            var selectors = this.options.selectors,
                menuItem = $(e.target).closest(selectors.topLevelItem),
                currentItem = $(selectors.menu).find(selectors.currentItem);

            menuItem.removeClass('_active');
            currentItem.addClass('_active');
        },

        /**
         * Add focus to active menu item
         */
        _keyboard: function (e) {
            var selectors = this.options.selectors,
                menuItem = $(e.target).closest(selectors.topLevelItem);

            if (e.which === 13) {
                this._close(e);
                $(selectors.topLevelHref, menuItem).focus();
            }
        },

        /**
         * Toggle active state on focus
         */
        _focus: function (e) {
            var selectors = this.options.selectors,
                menuItem = $(e.target).closest(selectors.topLevelItem);

            menuItem.addClass('_active')
                .siblings(selectors.topLevelItem)
                .removeClass('_active');
        },

        _closeSubmenu: function (e) {
            var selectors = this.options.selectors,
                currentItem = $(selectors.menu).find(selectors.currentItem);

            this._close(e);

            currentItem.addClass('_active');
        },

        _open: function (e) {
            var selectors = this.options.selectors,
                menuItemSelector = selectors.topLevelItem,
                menuItem = $(e.target).closest(menuItemSelector),
                subMenu = $(selectors.subMenu, menuItem),
                close = this._closeSubmenu.bind(this),
                closeBtn = subMenu.find(selectors.closeSubmenuBtn);


            if (subMenu.length) {
                e.preventDefault();
            }

            menuItem.addClass('_show')
                .siblings(menuItemSelector)
                .removeClass('_show');

            subMenu.attr('aria-expanded', 'true');

            //subMenu.css('height', subMenu.find('ul.menu')[0].height() + subMenu.find('strong.submenu-title').height());

            closeBtn.on('click', close);

            this.overlay.show(0).on('click', close);
            this.menuLinks.last().off('blur');
        },

        _close: function (e) {
            var selectors = this.options.selectors,
                menuItem = this.menu.find(selectors.topLevelItem + '._show'),
                subMenu = $(selectors.subMenu, menuItem),
                closeBtn = subMenu.find(selectors.closeSubmenuBtn),
                blur = this._blur.bind(this);

            e.preventDefault();

            this.overlay.hide(0).off('click');

            this.menuLinks.last().on('blur', blur);

            closeBtn.off('click');

            subMenu.attr('aria-expanded', 'false');

            menuItem.removeClass('_show _active');
        }
    });

    return $.mage.globalNavigation;
});

define('globalSearch', [
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.globalSearch', {
        options: {
            field: '.search-global-field',
            fieldActiveClass: '_active',
            input: '#search-global'
        },

        _create: function () {
            this.field = $(this.options.field);
            this.input = $(this.options.input);
            this._events();
        },

        _events: function () {
            var self = this;

            this.input
                .on('blur.resetGlobalSearchForm', function () {
                    if (!self.input.val()) {
                        self.field.removeClass(self.options.fieldActiveClass)
                    }
                });

            this.input
                .on('focus.activateGlobalSearchForm', function () {
                    self.field.addClass(self.options.fieldActiveClass)
                });
        }
    });

    return $.mage.globalSearch;
});

define('modalPopup', [
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.modalPopup', {
        options: {
            popup: '.popup',
            btnDismiss: '[data-dismiss="popup"]',
            btnHide: '[data-hide="popup"]'
        },

        _create: function () {
            this.fade = this.element;
            this.popup = $(this.options.popup, this.fade);
            this.btnDismiss = $(this.options.btnDismiss, this.popup);
            this.btnHide = $(this.options.btnHide, this.popup);

            this._events();
        },

        _events: function () {
            var self = this;

            this.btnDismiss
                .on('click.dismissModalPopup', function () {
                    self.fade.remove();
                });

            this.btnHide
                .on('click.hideModalPopup', function () {
                    self.fade.hide();
                });
        }
    });

    return $.mage.modalPopup;
});

define('useDefault', [
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.useDefault', {
        options: {
            field: '.field',
            useDefault: '.use-default',
            checkbox: '.use-default-control',
            label: '.use-default-label'
        },

        _create: function () {
            this.el = this.element;
            this.field = $(this.el).closest(this.options.field);
            this.useDefault = $(this.options.useDefault, this.field);
            this.checkbox = $(this.options.checkbox, this.useDefault);
            this.label = $(this.options.label, this.useDefault);
            this.origValue = this.el.attr('data-store-label');

            this._events();
        },

        _events: function () {
            var self = this;

            this.el
                .on('change.toggleUseDefaultVisibility keyup.toggleUseDefaultVisibility', $.proxy(this._toggleUseDefaultVisibility, this))
                .trigger('change.toggleUseDefaultVisibility');

            this.checkbox
                .on('change.setOrigValue', function () {
                    if ($(this).prop('checked')) {
                        self.el
                            .val(self.origValue)
                            .trigger('change.toggleUseDefaultVisibility');

                        $(this).prop('checked', false);
                    }
                });
        },

        _toggleUseDefaultVisibility: function () {
            var curValue = this.el.val(),
                origValue = this.origValue;

            this[curValue != origValue ? '_show' : '_hide']();
        },

        _show: function () {
            this.useDefault.show();
        },

        _hide: function () {
            this.useDefault.hide();
        }
    });

    return $.mage.useDefault;
});

define('loadingPopup', [
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.loadingPopup', {
        options: {
            message: 'Please wait...',
            timeout: 5000,
            timeoutId: null,
            callback: null,
            template: null
        },

        _create: function () {
            this.template =
                '<div class="popup popup-loading">' +
                '<div class="popup-inner">' + this.options.message + '</div>' +
                '</div>';

            this.popup = $(this.template);

            this._show();
            this._events();
        },

        _events: function () {
            var self = this;

            this.element
                .on('showLoadingPopup', function () {
                    self._show();
                })
                .on('hideLoadingPopup', function () {
                    self._hide();
                });
        },

        _show: function () {
            var options = this.options,
                timeout = options.timeout;

            $('body').trigger('processStart');

            if (timeout) {
                options.timeoutId = setTimeout(this._delayedHide.bind(this), timeout);
            }
        },

        _hide: function () {
            $('body').trigger('processStop');
        },

        _delayedHide: function () {
            this._hide();

            this.options.callback && this.options.callback();

            this.options.timeoutId && clearTimeout(this.options.timeoutId);
        }
    });

    return $.mage.loadingPopup;
});

define('collapsable', [
    'jquery',
    'jquery/ui',
    'jquery/jquery.tabs'
], function ($) {
    'use strict';

    $.widget('mage.collapsable', {
        options: {
            parent: null,
            openedClass: 'opened',
            wrapper: '.fieldset-wrapper'
        },

        _create: function () {
            this._events();
        },

        _events: function () {
            var self = this;

            this.element
                .on('show', function (e) {
                    var fieldsetWrapper = $(this).closest(self.options.wrapper);

                    fieldsetWrapper.addClass(self.options.openedClass);
                    e.stopPropagation();
                })
                .on('hide', function (e) {
                    var fieldsetWrapper = $(this).closest(self.options.wrapper);

                    fieldsetWrapper.removeClass(self.options.openedClass);
                    e.stopPropagation();
                });
        }
    });

    return $.mage.collapsable;
});

define('Magetop_Marketplace/js/theme', [
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/ie-class-fixer',
    'collapsable',
    'domReady!'
], function ($, keyboardHandler) {
    'use strict';

    /* @TODO refactor collapsable as widget and avoid logic binding with such a general selectors */
    $('.collapse').collapsable();

    $.each($('.entry-edit'), function (i, entry) {
        $('.collapse:first', entry).filter(function () {
            return $(this).data('collapsed') !== true;
        }).collapse('show');
    });

    keyboardHandler.apply();
});
