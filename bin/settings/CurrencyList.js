/**
 * @module package/quiqqer/currency/bin/CurrencyList
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/controls/buttons/Select
 * @require Ajax
 */
define('package/quiqqer/currency/bin/settings/CurrencyList', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Select',
    'Ajax'

], function (QUI, QUIControl, QUISelect, QUIAjax) {
    "use strict";

    return new Class({

        Type   : 'package/quiqqer/currency/bin/settings/CurrencyList',
        Extends: QUIControl,

        Binds: [
            '$onImport',
            '$onSelectChange'
        ],

        initialize: function (options) {
            this.parent(options);

            this.$Input  = null;
            this.$Select = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event : on import
         */
        $onImport: function () {
            this.$Input      = this.getElm();
            this.$Input.type = 'hidden';

            this.$Elm = new Element('div', {
                'class': 'quiqqer-currency-list'
            }).wraps(this.$Input);

            this.$Select = new QUISelect({
                showIcons: false,
                events   : {
                    onChange: this.$onSelectChange
                }
            }).inject(this.$Elm);

            this.getCurrencies().then(function (result) {

                for (var i in result) {
                    if (!result.hasOwnProperty(i)) {
                        continue;
                    }

                    this.$Select.appendChild(
                        result[i].text,
                        i
                    );
                }

                if (this.$Input.value !== '') {
                    this.$Select.setValue(this.$Input.value);
                }

            }.bind(this));
        },

        /**
         * Return all available currencies
         * @returns {Promise}
         */
        getCurrencies: function () {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_currency_ajax_getCurrencies', resolve, {
                    'package': 'quiqqer/currency',
                    onError  : reject
                });
            });
        },

        /**
         * event : on select change
         *
         * @param {String} value
         * @param {Object} Select - qui/controls/buttons/Select
         */
        $onSelectChange: function (value, Select) {
            this.$Input.value = value;
        }
    });
});
