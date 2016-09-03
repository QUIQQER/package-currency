/**
 * @module package/quiqqer/currency/bin/controls/Switch
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require css!package/quiqqer/currency/bin/controls/Switch.css'
 */
define('package/quiqqer/currency/bin/controls/Switch', [

    'qui/QUI',
    'qui/controls/Control',

    'css!package/quiqqer/currency/bin/controls/Switch.css'

], function (QUI, QUIControl) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/currency/bin/controls/Switch',

        Binds: [
            'open',
            'close',
            '$onInject',
            '$onReplace'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Display  = null;
            this.$DropDown = null;
            this.$Arrow    = null;

            this.addEvents({
                onInject : this.$onInject,
                onReplace: this.$onReplace
            });
        },

        /**
         * Return the DOMNode of the control
         *
         * @returns {HTMLDivElement}
         */
        create: function () {
            this.$Elm = new Element('div', {
                'class': 'quiqqer-currency-switch',
                html   : '<div class="quiqqer-currency-switch-display">' +
                         '<span class="fa fa-spinner fa-spin"></span>' +
                         '</div>' +
                         '<div class="quiqqer-currency-switch-dd"></div>'
            });

            this.$Display  = this.$Elm.getElement('.quiqqer-currency-switch-display');
            this.$DropDown = this.$Elm.getElement('.quiqqer-currency-switch-dd');

            return this.$Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            require(['package/quiqqer/currency/bin/Currency'], function (Currencies) {
                Promise.all([
                    Currencies.getCurrency(),
                    Currencies.getCurrencies()
                ]).then(function (result) {
                    var self       = this,
                        Currency   = result[0],
                        currencies = result[1];

                    this.$Display.set({
                        html : Currency.code,
                        title: Currency.text
                    });

                    if (!Object.getLength(currencies)) {
                        return;
                    }

                    Currencies.addEvent('onChange', function (Currencies, currencyCode) {

                        Currencies.getCurrency(currencyCode).then(function (Curr) {
                            self.$Display.set({
                                html : Curr.code,
                                title: Curr.text
                            });
                        });
                    });

                    this.$DropDown.setStyles({
                        display: 'none'
                    });

                    var entryClick = function (event) {
                        Currencies.setCurrency(event.target.get('data-code'));
                    };

                    var entryHover = function (event) {
                        event.target.addClass('hover');
                    };

                    var entryOut = function (event) {
                        event.target.removeClass('hover');
                    };

                    currencies.each(function (Entry) {
                        new Element('div', {
                            'class'    : 'quiqqer-currency-switch-dd-entry',
                            html       : Entry.code,
                            events     : {
                                mouseenter: entryHover,
                                mouseleave: entryOut,
                                click     : entryClick
                            },
                            'data-code': Entry.code
                        }).inject(this.$DropDown);
                    }.bind(this));

                    this.$Arrow = new Element('span', {
                        'class': 'fa fa-angle-down quiqqer-currency-switch-arrow'
                    }).inject(this.$Elm);

                    this.$Elm.set({
                        tabindex: -1,
                        styles  : {
                            outline       : 'none',
                            '-moz-outline': 'none'
                        }
                    });

                    this.$Elm.addClass('quiqqer-currency-switch__withArrow');
                    this.$Elm.addClass('button');

                    this.$Elm.addEvents({
                        click: function (event) {
                            event.target.focus();
                        },
                        focus: this.open,
                        blur : this.close
                    });


                    //currencies;
                }.bind(this));
            }.bind(this));
        },

        /**
         * event : on replace
         */
        $onReplace: function () {
            this.$onInject();
        },

        /**
         * Show the currency dropdown
         */
        open: function () {
            this.$DropDown.setStyles({
                display: 'inline'
            });
        },

        /**
         * Close the currency dropdown
         */
        close: function () {
            this.$DropDown.setStyles({
                display: 'none'
            });
        }
    });
});
