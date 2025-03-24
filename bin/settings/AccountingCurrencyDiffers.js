/**
 * @module package/quiqqer/currency/bin/settings/AccountingCurrencyDiffers
 * @author www.pcsg.de (www.pcsg.de)
 */
define('package/quiqqer/currency/bin/settings/AccountingCurrencyDiffers', [

    'qui/QUI',
    'qui/controls/Control'

], function (QUI, QUIControl) {
    "use strict";

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/currency/bin/settings/AccountingCurrencyDiffers',

        Binds: [
            '$onImport'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$AccountingCurrencyRow = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        $onImport: function () {
            var self = this,
                Table = this.getElm().getParent('table');

            this.getElm().addEvent('change', function () {

            });

            this.$AccountingCurrencyRow = Table.getElement(
                '[name="currency.accountingCurrency"]'
            ).getParent('tr');

            this.getElm().addEvent('change', function () {
                if (self.getElm().checked) {
                    self.enableAccountingCurrency();
                } else {
                    self.disableAccountingCurrency();
                }
            });

            if (this.getElm().checked) {
                this.enableAccountingCurrency();
            } else {
                this.disableAccountingCurrency();
            }
        },

        /**
         * enable accounting currency
         */
        enableAccountingCurrency: function () {
            this.$AccountingCurrencyRow.setStyle('display', null);
        },

        /**
         * disable accounting currency
         */
        disableAccountingCurrency: function () {
            this.$AccountingCurrencyRow.setStyle('display', 'none');
        }
    });
});
