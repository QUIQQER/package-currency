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

            this.$bulk = [];
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

                (() => {
                    this.convert();
                }).delay(this.getAttribute('delay'));
            }
        },

        /**
         * execute the conversion
         *
         * @return {Promise}
         */
        convert: function () {
            return new Promise((resolve) => {
                QUIAjax.get('package_quiqqer_currency_ajax_convertWithSign', (result) => {
                    this.fireEvent('done', [
                        this,
                        result
                    ]);

                    this.$bulk = []; // cleanup bulk
                    this.$running = false;
                    resolve();
                }, {
                    'package': 'quiqqer/currency',
                    data     : JSON.encode(this.$bulk),
                    onError  : resolve
                });
            });
        }
    });
});
