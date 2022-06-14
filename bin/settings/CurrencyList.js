/**
 * @module package/quiqqer/currency/bin/CurrencyList
 * @author www.pcsg.de (Henning Leutz)
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

            this.$Input = null;
            this.$Select = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event : on import
         */
        $onImport: function () {
            this.$Input = this.getElm();
            this.$Input.type = 'hidden';

            this.$Elm = new Element('div', {
                'class': 'quiqqer-currency-list'
            }).wraps(this.$Input);

            this.$Select = new QUISelect({
                showIcons: false,
                events   : {
                    onChange: this.$onSelectChange
                },
                styles   : {
                    width: '100%'
                }
            }).inject(this.$Elm);

            this.getCurrencies().then(function (result) {
                for (let i in result) {
                    if (!result.hasOwnProperty(i)) {
                        continue;
                    }

                    if (result[i].text === '') {
                        result[i].text = result[i].code;
                    }

                    this.$Select.appendChild(
                        result[i].text,
                        result[i].code
                    );
                }

                if (this.$Input.value !== '') {
                    this.$Select.setValue(this.$Input.value);
                }
            }.bind(this));

            if (this.getElm().getParent().hasClass('field-container')) {
                this.$Elm.setStyle('flexGrow', 1);
            }
        },

        /**
         * Return all available currencies
         * @returns {Promise}
         */
        getCurrencies: function () {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_currency_ajax_getAllowedCurrencies', resolve, {
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
            this.fireEvent('change', [
                this,
                value
            ]);
        }
    });
});
