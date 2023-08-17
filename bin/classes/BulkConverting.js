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
        Type: 'package/quiqqer/currency/bin/classes/BulkConverting',

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
            // if already at the converting list
            // we don't need it
            for (let i = 0, len = this.$bulk.length; i < len; i++) {
                if (this.$bulk.amount === amount && this.$bulk.from === currencyFrom && this.$bulk.to === currencyTo) {
                    return;
                }
            }

            this.$bulk.push({
                amount: amount,
                from: currencyFrom,
                to: currencyTo,
                id: String.uniqueID()
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
            let list = {};

            this.$bulk.forEach((bulkEntry) => {
                list[bulkEntry.id] = bulkEntry;
            });

            return new Promise((resolve) => {
                QUIAjax.get('package_quiqqer_currency_ajax_convertWithSign', (result) => {
                    this.fireEvent('done', [
                        this,
                        result
                    ]);

                    this.$cleanupBulk(result);
                    this.$running = false;

                    if (this.$bulk.length) {
                        return this.convert().then(resolve);
                    }

                    resolve();
                }, {
                    'package': 'quiqqer/currency',
                    data: JSON.encode(list),
                    onError: resolve
                });
            });
        },

        $cleanupBulk: function (result) {
            const resultAmounts = new Set(result.map(item => item.amount));
            const filteredBulk = this.$bulk.filter(item => !resultAmounts.has(item.amount));

            this.$bulk = filteredBulk;
        }
    });
});
