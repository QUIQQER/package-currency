/**
 * @module package/quiqqer/currency/bin/settings/CurrencyWindow
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require Locale
 * @require package/quiqqer/currency/bin/settings/Currency
 */
define('package/quiqqer/currency/bin/settings/CurrencyWindow', [

    'qui/QUI',
    'qui/controls/windows/Confirm',
    'Locale',
    'package/quiqqer/currency/bin/settings/Currency'

], function (QUI, QUIConfirm, QUILocale, Currency) {
    "use strict";

    return new Class({
        Extends: QUIConfirm,
        Type   : 'package/quiqqer/currency/bin/settings/CurrencyWindow',

        Binds: [
            '$onOpen',
            '$onSubmit'
        ],

        options: {
            currency : false,
            icon     : 'fa fa-money',
            title    : false,
            texticon : false,
            autoclose: false,
            maxHeight: 400,
            maxWidth : 600
        },

        initialize: function (options) {
            this.setAttribute(
                'title',
                QUILocale.get('quiqqer/currency', 'control.currency.title', {
                    currency: options.currency
                })
            );

            this.parent(options);

            this.$Currency = null;

            this.addEvents({
                onOpen  : this.$onOpen,
                onSubmit: this.$onSubmit
            });
        },

        /**
         * event : on open
         */
        $onOpen: function () {
            this.$Currency = new Currency({
                currency: this.getAttribute('currency')
            }).inject(this.getContent());
        },

        /**
         * event : on submit
         */
        $onSubmit: function () {
            this.Loader.show();

            this.$Currency.save().then(function () {
                this.Loader.hide();
                this.close();
            }.bind(this));
        }
    });
});
