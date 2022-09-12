/**
 * @module package/quiqqer/currency/bin/controls/Switch
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onChangeCurrency [this, CurrencyData]
 * @event QUI:: onQuiqqerCurrencyChange [this, CurrencyData]
 */
define('package/quiqqer/currency/bin/controls/Switch', [

    'qui/QUI',
    'qui/controls/Control',
    'Ajax',
    'package/quiqqer/currency/bin/Currency',

    'css!package/quiqqer/currency/bin/controls/Switch.css'

], function (QUI, QUIControl, QUIAjax, Currencies) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/currency/bin/controls/Switch',

        Binds: [
            'open',
            'close',
            '$onInject',
            '$onReplace',
            '$onChange'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Display = null;
            this.$DropDown = null;
            this.$Arrow = null;

            this.addEvents({
                onInject : this.$onInject,
                onReplace: this.$onReplace,
                onDestroy: () => {
                    Currencies.removeEvent('onChange', this.$onChange);
                }
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

            this.$Display = this.$Elm.getElement('.quiqqer-currency-switch-display');
            this.$DropDown = this.$Elm.getElement('.quiqqer-currency-switch-dd');

            return this.$Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            Promise.all([
                Currencies.getCurrency(),
                Currencies.getCurrencies()
            ]).then((result) => {
                const Currency   = result[0],
                      currencies = result[1];

                this.$Display.set({
                    html : Currency.code,
                    title: Currency.text
                });

                if (!Object.getLength(currencies)) {
                    return;
                }

                Currencies.addEvent('onChange', this.$onChange);

                this.$DropDown.setStyles({
                    display: 'none'
                });

                const entryClick = function (event) {
                    Currencies.setCurrency(event.target.get('data-code'));
                };

                const entryHover = function (event) {
                    event.target.addClass('hover');
                };

                const entryOut = function (event) {
                    event.target.removeClass('hover');
                };

                currencies.each((Entry) => {
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
                });

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
                
                /*
                this.fireEvent('changeCurrency', [
                    this,
                    Curr
                ]);

                QUI.fireEvent('quiqqerCurrencyChange', [
                    this,
                    Curr
                ]);
                */
            });
        },

        /**
         * event: on change
         *
         * @param Currencies
         * @param currencyCode
         */
        $onChange: function (Currencies, currencyCode) {
            const self = this;

            Currencies.getCurrency(currencyCode).then(function (Curr) {
                self.$Display.set({
                    html : Curr.code,
                    title: Curr.text
                });

                QUIAjax.post('package_quiqqer_currency_ajax_setUserCurrency', function () {
                    self.fireEvent('changeCurrency', [
                        self,
                        Curr
                    ]);

                    QUI.fireEvent('quiqqerCurrencyChange', [
                        self,
                        Curr
                    ]);
                }, {
                    'package': 'quiqqer/currency',
                    currency : Curr.code
                });
            });
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
