/**
 * @module package/quiqqer/currency/bin/controls/currency/SelectItem
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/currency/bin/controls/SelectItem', [

    'qui/controls/Control',
    'package/quiqqer/currency/bin/Currency',

    'css!package/quiqqer/currency/bin/controls/SelectItem.css'

], function (QUIControl, Currencies) {
    'use strict';

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/currency/bin/controls/SelectItem',

        Binds: [
            '$onInject'
        ],

        options: {
            id: false
        },

        initialize: function (options) {
            this.parent(options);

            this.$Icon = null;
            this.$Text = null;
            this.$Destroy = null;

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * Return the DOMNode Element
         *
         * @returns {HTMLElement}
         */
        create: function () {
            const self = this,
                Elm = this.parent();

            Elm.set({
                'class': 'quiqqer-currency-selectItem smooth',
                html: '<span class="quiqqer-currency-selectItem-icon fa fa-money"></span>' +
                    '<span class="quiqqer-currency-selectItem-text">&nbsp;</span>' +
                    '<span class="quiqqer-currency-selectItem-destroy fa fa-remove"></span>'
            });

            this.$Icon = Elm.getElement('.quiqqer-currency-selectItem-icon');
            this.$Text = Elm.getElement('.quiqqer-currency-selectItem-text');
            this.$Destroy = Elm.getElement('.quiqqer-currency-selectItem-destroy');

            this.$Destroy.addEvent('click', function () {
                self.destroy();
            });

            return Elm;
        },

        /**
         * event : on inject
         */
        $onInject: function () {
            this.$Text.set({
                html: '<span class="fa fa-spinner fa-spin"></span>'
            });

            Currencies.getCurrency(this.getAttribute('id')).then((data) => {
                this.$Text.set('html', '#' + data.code + ' - <b>' + data.text + '</b>');
                this.getElm().set('title', '#' + data.code + ' - ' + data.text);
            }).catch(() => {
                this.$Icon.removeClass('fa-money');
                this.$Icon.addClass('fa-bolt');
                this.$Text.set('html', '...');

                this.$Destroy.click();
            });
        }
    });
});
