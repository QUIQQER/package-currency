/**
 * Currency Handler
 *
 * @module package/quiqqer/currency/bin/Currency
 * @author wwww.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/classes/DOM
 * @require Ajax
 */
define('package/quiqqer/currency/bin/Currency', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax'

], function (QUI, QUIDOM, Ajax) {
    "use strict";

    var def = 'EUR';

    if (typeof DEFAULT_CURRENCY !== 'undefined') {
        def = DEFAULT_CURRENCY;
    }

    var Currencies = new Class({
        Extends: QUIDOM,
        Type   : 'package/quiqqer/currency/bin/Currency',

        initialize: function (options) {
            this.parent(options);

            this.$currency   = def;
            this.$currencies = {};
        },

        /**
         * Change the global currency
         *
         * @param {String} currencyCode
         */
        setCurrency: function (currencyCode) {

            this.getCurrencies().then(function (currencies) {

                var found = currencies.find(function (Currency) {
                    return Currency.code == currencyCode;
                });

                if (found) {
                    this.$currency = found.code;
                    this.fireEvent('change', [this, found.code]);
                }

            }.bind(this));
        },

        /**
         * Return the current Currency
         *
         * @return {Promise}
         */
        getCurrency: function () {
            return new Promise(function (resolve, reject) {

                var currencyCode = this.$currency;

                this.getCurrencies().then(function (currencies) {

                    var found = currencies.find(function (Currency) {
                        return Currency.code == currencyCode;
                    });

                    if (found) {
                        return resolve(found);
                    }

                    return reject();
                });

            }.bind(this));
        },

        /**
         * Return all available currencies
         *
         * @returns {Promise}
         */
        getCurrencies: function () {
            var self = this;

            if (Object.getLength(this.$currencies)) {
                return Promise.resolve(this.$currencies);
            }

            return new Promise(function (resolve, reject) {
                Ajax.get('package_quiqqer_currency_ajax_getAllowedCurrencies', function (result) {
                    self.$currencies = result;

                    resolve(self.$currencies);
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
            currencyTo   = currencyTo || this.$currency;
            currencyFrom = currencyFrom || this.$currency;

            return new Promise(function (resolve) {
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
            currencyTo   = currencyTo || this.$currency;
            currencyFrom = currencyFrom || this.$currency;

            if (!amount) {
                amount = 0;
            }

            console.log(amount, currencyFrom, currencyTo);

            return new Promise(function (resolve) {
                Ajax.get('package_quiqqer_currency_ajax_convertWithSign', resolve, {
                    'package'   : 'quiqqer/currency',
                    amount      : amount,
                    currencyFrom: currencyFrom,
                    currencyTo  : currencyTo
                });
            });
        }
    });

    return new Currencies();
});
