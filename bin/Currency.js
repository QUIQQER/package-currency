/**
 * Currency Handler
 *
 * @require qui/QUI
 * @require Ajax
 */
define('package/quiqqer/currency/bin/Currency', [

    'qui/QUI',
    'Ajax'

], function (QUI, Ajax) {
    "use strict";

    var def = 'EUR';

    if (typeof DEFAULT_CURRENCY !== 'undefined') {
        def = DEFAULT_CURRENCY;
    }

    return {
        currency: def,

        /**
         * Convert an amount, the result is without the currency sign
         *
         * @param {Number} amount
         * @param {String} currencyFrom
         * @param {String} currencyTo
         * @returns {Promise}
         */
        convert: function (amount, currencyFrom, currencyTo) {
            currencyTo   = currencyTo || this.currency;
            currencyFrom = currencyFrom || this.currency;

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
            currencyTo   = currencyTo || this.currency;
            currencyFrom = currencyFrom || this.currency;

            if (!amount) {
                amount = 0;
            }

            return new Promise(function (resolve) {
                Ajax.get('package_quiqqer_currency_ajax_convertWithSign', resolve, {
                    'package'   : 'quiqqer/currency',
                    amount      : amount,
                    currencyFrom: currencyFrom,
                    currencyTo  : currencyTo
                });
            });
        }
    };
});
