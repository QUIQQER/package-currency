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
            '$onImport',
            '$onInject',
            '$onReplace',
            '$onChange',
            '$getBtnCurrencyCodeHtml',
            '$getBtnCurrencySignHtml'
        ],

        options: {
            buttonshowsign  : 0, // '1' enables currency sign in button
            dropdownshowsign: 1, // '1' enables currency sign in the dropdown
            showarrow       : 1, // enable button arrow down
            showloader      : 1, // enable button arrow down
            dropdownposition: 'left', // 'right', 'left'. stick to right or left bottom control corner
        },

        initialize: function (options) {
            this.parent(options);

            this.$Display          = null;
            this.$DropDown         = null;
            this.$Arrow            = null;
            this.$buttonSign       = false;
            this.$dropdownShowSign = false;
            this.$controlImported  = false;

            this.addEvents({
                onImport : this.$onImport,
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
            if (parseInt(this.getAttribute('buttonshowsign')) === 1) {
                this.$buttonSign = true;
            }

            if (parseInt(this.getAttribute('dropdownshowsign')) === 1) {
                this.$dropdownShowSign = true;
            }

            let loaderHtml = '';
            if (parseInt(this.getAttribute('showloader')) === 1) {
                loaderHtml = '<span class="fa fa-spinner fa-spin"></span>';
            }

            this.$Elm = new Element('div', {
                'class': 'quiqqer-currency-switch',
                html   : '<div class="quiqqer-currency-switch-display">' +
                    loaderHtml +
                    '</div>' +
                    '<div class="quiqqer-currency-switch-dd"></div>'
            });

            this.$Display  = this.$Elm.getElement('.quiqqer-currency-switch-display');
            this.$DropDown = this.$Elm.getElement('.quiqqer-currency-switch-dd');

            if (this.getAttribute('dropdownposition') === 'right') {
                this.$DropDown.classList.add('right');
            } else {
                this.$DropDown.classList.add('left');
            }

            return this.$Elm;
        },

        $onImport: function () {
            this.$controlImported = true;
            this.$onInject();
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            Promise.all([
                Currencies.getCurrency(),
                Currencies.getCurrencies()
            ]).then((result) => {

                // create control body if control use "onImport"
                if (this.$controlImported) {
                    this.$Elm.set('html', '');
                    const Main = this.$Elm;
                    this.create().replaces(Main);
                }

                const Currency   = result[0],
                      currencies = result[1];

                this.$Display.set({
                    html : this.$getBtnCurrencySignHtml(Currency.sign) + this.$getBtnCurrencyCodeHtml(Currency.code),
                    title: Currency.text
                });

                if (!Object.getLength(currencies)) {
                    this.$Elm.classList.add('inactive');
                    return;
                }

                Currencies.addEvent('onChange', this.$onChange);

                this.$DropDown.setStyles({
                    display: 'none'
                });

                const entryClick = function (event) {
                    Currencies.setCurrency(event.target.get('data-code'));
                };


                currencies.each((Entry) => {
                    new Element('div', {
                        'class'    : 'quiqqer-currency-switch-dd-entry',
                        html       : this.$getDropdownCurrencySignHtml(Entry.sign) + this.$getDropdownCurrencyCodeHtml(
                            Entry.code),
                        events     : {
                            click: entryClick
                        },
                        'data-code': Entry.code
                    }).inject(this.$DropDown);
                });

                if (parseInt(this.getAttribute('showarrow')) === 1) {
                    this.$Arrow = new Element('span', {
                        'class': 'fa fa-angle-down quiqqer-currency-switch-arrow'
                    }).inject(this.$Elm);
                }

                this.$Elm.set({
                    tabindex: -1,
                    styles  : {
                        outline       : 'none',
                        '-moz-outline': 'none'
                    }
                });

                this.$Elm.addEvents({
                    click: function (event) {
                        event.target.focus();
                    },
                    focus: this.open,
                    blur : this.close
                });
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
                    html : self.$getBtnCurrencySignHtml(Curr.sign) + self.$getBtnCurrencyCodeHtml(Curr.code),
                    title: Curr.text
                });

                QUIAjax.post('package_quiqqer_currency_ajax_setUserCurrency', function () {
                    window.DEFAULT_USER_CURRENCY = Curr;

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
        },

        /**
         * Get button currency code html string
         *
         * @param code
         * @returns {string}
         */
        $getBtnCurrencyCodeHtml: function (code) {
            return '<span class="quiqqer-currency-switch-code">' + code + '</span>';
        },

        /**
         * Get button currency signal html string
         *
         * @param sign
         * @returns {string}
         */
        $getBtnCurrencySignHtml: function (sign) {
            if (!this.$buttonSign) {
                return '';
            }

            return '<span class="quiqqer-currency-switch-sign">' + sign + '</span>';
        },

        /**
         * Get dropdown currency html string
         *
         * @param code
         * @returns {string}
         */
        $getDropdownCurrencyCodeHtml: function (code) {
            return '<span class="quiqqer-currency-switch-dd-code">' + code + '</span>';
        },

        /**
         * Get dropdown currency sign html string
         *
         * @param sign
         * @returns {string}
         */
        $getDropdownCurrencySignHtml: function (sign) {
            if (!this.$dropdownShowSign) {
                return '';
            }

            return '<span class="quiqqer-currency-switch-dd-sign">' + sign + '</span>';
        }
    });
});
