/**
 * Allowed currencies
 * Control for currency administration
 *
 * @module package/quiqqer/currency/bin/settings/AllowedCurrencies
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/controls/buttons/Switch
 * @require Ajax
 * @require css!package/quiqqer/currency/bin/settings/AllowedCurrencies.css
 */
define('package/quiqqer/currency/bin/settings/AllowedCurrencies', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Switch',
    'Ajax',

    'css!package/quiqqer/currency/bin/settings/AllowedCurrencies.css'

], function (QUI, QUIControl, QUISwitch, QUIAjax) {
    "use strict";

    return new Class({

        Type   : 'package/quiqqer/currency/bin/settings/AllowedCurrencies',
        Extends: QUIControl,

        Binds: [
            '$onImport',
            '$onCurrencyStatusChange',
            '$switchCurrencyStatus'
        ],

        options: {
            values: {}
        },

        initialize: function (options) {
            this.parent(options);

            this.$Input     = null;
            this.$Elm       = null;
            this.$Container = null;

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

            if (this.$Input.value !== '') {
                var values = {};
                var value  = this.$Input.value.split(',');

                for (var i = 0, len = value.length; i < len; i++) {
                    values[value[i]] = 1;
                }

                this.setAttribute('values', values);
            }

            this.$Elm = new Element('div', {
                'class': 'quiqqer-currency-allowed',
                html   : '<div class="quiqqer-currency-allowed-container">' +
                         '<span class="fa fa-spinner fa-spin"></span>' +
                         '</div>'
            }).wraps(this.$Input);

            this.$Container = this.$Elm.getElement(
                '.quiqqer-currency-allowed-container'
            );

            this.getCurrencies().then(function (list) {

                var i, Container, CurrencySwitch;

                var values = this.getAttribute('values');

                this.$Container.set('html', '');


                for (i in list) {
                    if (!list.hasOwnProperty(i)) {
                        continue;
                    }

                    Container = new Element('div', {
                        'class': 'quiqqer-currency-allowed-currency'
                    }).inject(this.$Container);

                    CurrencySwitch = new QUISwitch({
                        title   : list[i].text,
                        currency: i,
                        events  : {
                            onChange: this.$onCurrencyStatusChange
                        }
                    }).inject(Container);

                    new Element('span', {
                        'class': 'quiqqer-currency-allowed-currency-text',
                        html   : list[i].text + ' ' + list[i].sign,
                        events : {
                            click: this.$switchCurrencyStatus
                        }
                    }).inject(Container);

                    if (!values.hasOwnProperty(i)) {
                        values[i] = 0;
                    }

                    if (values[i] === 1) {
                        CurrencySwitch.on();
                    } else {
                        CurrencySwitch.off();
                    }
                }

                this.setAttribute('values', values);

            }.bind(this));
        },

        /**
         * update values to the input field
         */
        update: function () {
            var allowed = [],
                values  = this.getAttribute('values');

            if (typeOf(values) === 'object') {
                for (var i in values) {
                    if (!values.hasOwnProperty(i)) {
                        continue;
                    }

                    if (values[i]) {
                        allowed.push(i);
                    }
                }
            }

            this.$Input.value = allowed.join(',');
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
         * event : on currency status change
         * @param Switch
         */
        $onCurrencyStatusChange: function (Switch) {
            var currency = Switch.getAttribute('currency');
            var values   = this.getAttribute('values');

            values[currency] = Switch.getStatus() ? 1 : 0;

            this.setAttribute('values', values);
            this.update();
        },

        /**
         * event : click on span -> currency status change
         *
         * @param {Event} event
         */
        $switchCurrencyStatus: function (event) {
            var Target          = event.target;
            var SwitchContainer = Target.getParent().getElement('.qui-switch');

            var Switch = QUI.Controls.getById(
                SwitchContainer.get('data-quiid')
            );

            Switch.toggle();
        }
    });
});
