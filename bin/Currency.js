/**
 * Currency Handler
 *
 * @module package/quiqqer/currency/bin/Currency
 * @author wwww.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/currency/bin/Currency', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax',
    'package/quiqqer/currency/bin/classes/BulkConverting'

], function (QUI, QUIDOM, Ajax, BulkConverting) {
    "use strict";

    let Converter = null;
    let def = 'EUR';
    let SYSTEM_CURRENCY = '';


    // package_quiqqer_currency_ajax_setUserCurrency
    if (typeof window.DEFAULT_CURRENCY !== 'undefined') {
        def = window.DEFAULT_CURRENCY;
        SYSTEM_CURRENCY = def;
    }

    if (typeof window.DEFAULT_USER_CURRENCY !== 'undefined') {
        def = window.DEFAULT_USER_CURRENCY.code;
        SYSTEM_CURRENCY = def;
    }

    const Currencies = new Class({

        Extends: QUIDOM,
        Type   : 'package/quiqqer/currency/bin/Currency',

        initialize: function (options) {
            this.parent(options);

            this.$currency = def;
            this.$currencies = {};

            if (SYSTEM_CURRENCY !== this.$currency) {
                this.fireEvent('change', [
                    this,
                    this.$currency
                ]);
            }
        },

        /**
         * Change the global currency
         *
         * @param {String} currencyCode
         */
        setCurrency: function (currencyCode) {
            this.getCurrencies().then(function (currencies) {
                const found = currencies.find(function (Currency) {
                    return Currency.code === currencyCode;
                });

                if (found) {
                    this.$currency = found.code;
                    this.fireEvent('change', [
                        this,
                        found.code
                    ]);
                }
            }.bind(this));
        },

        /**
         * Return the data of a wanted currency
         *
         * @param {String} [currencyCode] - optional, default = current currency
         * @return {Promise}
         */
        getCurrency: function (currencyCode) {
            currencyCode = currencyCode || this.$currency;

            return new Promise((resolve, reject) => {
                this.getCurrencies().then((currencies) => {
                    const found = currencies.find((Currency) => {
                        return Currency.code === currencyCode;
                    });

                    if (found) {
                        return resolve(found);
                    }

                    return reject();
                });
            });
        },

        /**
         * Return all available currencies
         *
         * @returns {Promise}
         */
        getCurrencies: function () {
            if (Object.getLength(this.$currencies)) {
                return Promise.resolve(this.$currencies);
            }

            return new Promise((resolve, reject) => {
                Ajax.get('package_quiqqer_currency_ajax_getAllowedCurrencies', (result) => {
                    this.$currencies = result;

                    resolve(this.$currencies);
                }, {
                    'package': 'quiqqer/currency',
                    onError  : reject
                });
            });
        },

        /**
         * Convert an amount, the result is without the currency sign
         *
         * @param {Number} amount
         * @param {String} currencyFrom
         * @param {String} currencyTo
         * @returns {Promise}
         */
        convert: function (amount, currencyFrom, currencyTo) {
            currencyTo = currencyTo || this.$currency;
            currencyFrom = currencyFrom || this.$currency;

            return new Promise((resolve) => {
                Ajax.get('package_quiqqer_currency_ajax_convert', resolve, {
                    'package'   : 'quiqqer/currency',
                    amount      : amount,
                    currencyFrom: currencyFrom,
                    currencyTo  : currencyTo
                });
            });
        },

        /**
         * Convert an amount, the result is with the currency sign
         *
         * @param {Number} amount
         * @param {String} currencyFrom
         * @param {String} currencyTo
         * @returns {Promise}
         */
        convertWithSign: function (amount, currencyFrom, currencyTo) {
            currencyTo = currencyTo || this.$currency;
            currencyFrom = currencyFrom || this.$currency;

            if (!amount) {
                amount = 0;
            }

            if (!Converter) {
                Converter = new BulkConverting();
            }

            if (Converter.isRunning() === false) {
                Converter.removeEvents('onDone');
            }

            Converter.add(amount, currencyFrom, currencyTo);

            return new Promise((resolve) => {
                Converter.addEvent('onDone', (Cnv, result) => {
                    if (!result) {
                        return;
                    }

                    let i = 0, len = result.length;

                    for (; i < len; i++) {
                        if (result[i].amount == amount &&
                            result[i].from === currencyFrom &&
                            result[i].to === currencyTo) {
                            break;
                        }
                    }

                    resolve(result[i].converted);
                });
            });
        }
    });

    return new Currencies();
});
