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

    var Converter = null;
    var def       = 'EUR';

    if (typeof window.DEFAULT_CURRENCY !== 'undefined') {
        def = window.DEFAULT_CURRENCY;
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
                    return Currency.code === currencyCode;
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
                        return Currency.code === currencyCode;
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

            if (!Converter) {
                Converter = new BulkConverting();
            }

            if (Converter.isRunning() === false) {
                Converter.removeEvents('onDone');
            }

            Converter.add(amount, currencyFrom, currencyTo);

            return new Promise(function (resolve) {
                Converter.addEvent('onDone', function (Cnv, result) {
                    for (var i = 0, len = result.length; i < len; i++) {
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
