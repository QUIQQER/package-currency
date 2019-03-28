/**
 * Converting multiple amounts
 *
 * @module package/quiqqer/currency/bin/classes/BulkConverting
 * @author wwww.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/currency/bin/classes/BulkConverting', [

    'qui/QUI',
    'qui/classes/DOM',
    'Ajax'

], function (QUI, QUIDOM, QUIAjax) {
    "use strict";

    return new Class({
        Extends: QUIDOM,
        Type   : 'package/quiqqer/currency/bin/classes/BulkConverting',

        options: {
            delay: 200
        },

        initialize: function (options) {
            this.parent(options);

            this.$bulk    = [];
            this.$running = false;
        },

        /**
         * Is the bulk currently running
         *
         * @return {boolean}
         */
        isRunning: function () {
            return this.$running;
        },

        /**
         * add a conversion
         *
         * @param amount
         * @param currencyFrom
         * @param currencyTo
         */
        add: function (amount, currencyFrom, currencyTo) {
            this.$bulk.push({
                amount: amount,
                from  : currencyFrom,
                to    : currencyTo
            });

            if (!this.$running) {
                this.$running = true;

                (function () {
                    this.convert();
                }).delay(this.getAttribute('delay'), this);
            }
        },

        /**
         * execute the conversion
         *
         * @return {Promise}
         */
        convert: function () {
            var self = this;

            return new Promise(function () {
                QUIAjax.get('package_quiqqer_currency_ajax_convertWithSign', function (result) {
                    self.fireEvent('done', [this, result]);
                    self.$running = false;
                }, {
                    'package': 'quiqqer/currency',
                    'data'   : JSON.encode(self.$bulk)
                });
            });
        }
    });
});
