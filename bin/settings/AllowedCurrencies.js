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
 * @require Locale
 * @require controls/grid/Grid
 * @require css!package/quiqqer/currency/bin/settings/AllowedCurrencies.css
 */
define('package/quiqqer/currency/bin/settings/AllowedCurrencies', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Switch',
    'Ajax',
    'Locale',
    'controls/grid/Grid',

    'css!package/quiqqer/currency/bin/settings/AllowedCurrencies.css'

], function (QUI, QUIControl, QUISwitch, QUIAjax, QUILocale, Grid) {
    "use strict";

    var lg = 'quiqqer/currency';

    return new Class({

        Type   : 'package/quiqqer/currency/bin/settings/AllowedCurrencies',
        Extends: QUIControl,

        Binds: [
            '$onImport',
            '$onCurrencyStatusChange',
            '$switchCurrencyStatus',
            '$changeAutoUpdate',
            '$importFromECB'
        ],

        options: {
            values: {}
        },

        initialize: function (options) {
            this.parent(options);

            this.$Input     = null;
            this.$Elm       = null;
            this.$Container = null;
            this.$Grid      = null;

            this.addEvents({
                onImport: this.$onImport
            });
        },

        /**
         * event : on import
         */
        $onImport: function () {
            var i, len;

            this.$Input      = this.getElm();
            this.$Input.type = 'hidden';

            if (this.$Input.value !== '') {
                var values = {};
                var value  = this.$Input.value.split(',');

                for (i = 0, len = value.length; i < len; i++) {
                    values[value[i]] = 1;
                }

                this.setAttribute('values', values);
            }

            this.$Elm = new Element('div', {
                'class': 'quiqqer-currency-allowed',
                html   : '<div class="quiqqer-currency-allowed-container"></div>'
            }).wraps(this.$Input);

            var Settings = this.$Elm.getParent('.qui-xml-panel-row-item');

            if (Settings) {
                Settings.setStyles({
                    height  : 300,
                    overflow: 'hidden',
                    width   : '100%'
                });
            }

            this.$Container = this.$Elm.getElement(
                '.quiqqer-currency-allowed-container'
            );

            var width = this.$Container.getSize().x;

            this.$Grid = new Grid(this.$Container, {
                pagination : true,
                height     : 300,
                width      : width,
                columnModel: [{
                    header   : QUILocale.get(lg, 'grid.setting.currency'),
                    dataIndex: 'code',
                    dataType : 'string',
                    width    : 60,
                    editable : true
                }, {
                    header   : QUILocale.get(lg, 'grid.setting.sign'),
                    dataIndex: 'sign',
                    dataType : 'string',
                    width    : 60,
                    editable : true
                }, {
                    header   : QUILocale.get(lg, 'grid.setting.rate'),
                    dataIndex: 'rate',
                    dataType : 'string',
                    width    : 100
                }, {
                    header   : QUILocale.get(lg, 'grid.setting.allowed'),
                    dataIndex: 'allowed',
                    dataType : 'QUI',
                    width    : 100
                }, {
                    header   : QUILocale.get(lg, 'grid.setting.update'),
                    dataIndex: 'autoupdate',
                    dataType : 'QUI',
                    width    : 100
                }],
                buttons    : [{
                    name     : 'add',
                    text     : 'Währung hinzufügen',
                    textimage: 'fa fa-plus'
                }, {
                    name     : 'edit',
                    text     : 'Währung editieren',
                    textimage: 'fa fa-edit',
                    disabled : true
                }, {
                    type: 'seperator'
                }, {
                    name     : 'delete',
                    text     : 'Währung löschen',
                    textimage: 'fa fa-trash',
                    disabled : true
                }]
            });

            this.$Grid.setWidth(width);

            this.$Grid.addEvents({
                click  : function () {

                },
                refresh: function () {
                    this.getCurrencies().then(function (list) {
                        var data   = [],
                            values = this.getAttribute('values');

                        for (var i in list) {
                            if (!list.hasOwnProperty(i)) {
                                continue;
                            }

                            list[i].allowed = new QUISwitch({
                                status: (typeof values[i] !== 'undefined')
                            });

                            list[i].autoupdate = new QUISwitch({
                                status  : list[i].autoupdate,
                                currency: list[i].code,
                                events  : {
                                    onChange: this.$changeAutoUpdate
                                }
                            });

                            data.push(list[i]);
                        }

                        this.$Grid.setData({
                            data: data
                        });
                    }.bind(this));
                }.bind(this)
            });

            this.$Grid.refresh();
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
        },

        /**
         * Set the currency autoupdate status
         *
         * @param {Object} Switch
         * @return {Promise}
         */
        $changeAutoUpdate: function (Switch) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_currency_ajax_setAutoupdate', resolve, {
                    'package' : 'quiqqer/currency',
                    currency  : Switch.getAttribute('currency'),
                    autoupdate: Switch.getStatus() ? 1 : 0,
                    onError   : reject
                });
            });
        },

        /**
         * Import the currencies from the ECB
         *
         * @returns {Promise}
         */
        $importFromECB: function () {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_currency_ajax_importFromECB', resolve, {
                    'package': 'quiqqer/currency',
                    onError  : reject
                });
            });
        }
    });
});
