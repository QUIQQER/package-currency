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
    'qui/controls/windows/Confirm',
    'qui/controls/windows/Prompt',
    'Ajax',
    'Locale',
    'controls/grid/Grid',
    'package/quiqqer/currency/bin/settings/CurrencyWindow',

    'css!package/quiqqer/currency/bin/settings/AllowedCurrencies.css'

], function (QUI, QUIControl, QUISwitch, QUIConfirm, QUIPrompt, QUIAjax, QUILocale, Grid, CurrencyWindow) {
    "use strict";

    var lg = 'quiqqer/currency';

    return new Class({

        Type   : 'package/quiqqer/currency/bin/settings/AllowedCurrencies',
        Extends: QUIControl,

        Binds: [
            'refresh',
            'openCreateDialog',
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

            var self     = this,
                Settings = this.$Elm.getParent('.qui-xml-panel-row-item');

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
                    textimage: 'fa fa-plus',
                    events   : {
                        onClick: this.openCreateDialog
                    }
                }, {
                    name     : 'edit',
                    text     : 'Währung editieren',
                    textimage: 'fa fa-edit',
                    disabled : true,
                    events   : {
                        onClick: function () {
                            self.openUpdateDialog(self.$Grid.getSelectedData()[0].code);
                        }
                    }
                }, {
                    type: 'seperator'
                }, {
                    name     : 'delete',
                    text     : 'Währung löschen',
                    textimage: 'fa fa-trash',
                    disabled : true,
                    events   : {
                        onClick: function () {
                            self.openDeleteDialog(self.$Grid.getSelectedData()[0].code);
                        }
                    }
                }]
            });

            this.$Grid.setWidth(width);

            this.$Grid.addEvents({
                onClick: function () {
                    var selected = self.$Grid.getSelectedIndices(),
                        buttons  = self.$Grid.getButtons();

                    var Edit = buttons.filter(function (Btn) {
                        return Btn.getAttribute('name') == 'edit';
                    })[0];

                    var Delete = buttons.filter(function (Btn) {
                        return Btn.getAttribute('name') == 'delete';
                    })[0];

                    if (selected.length) {
                        Edit.enable();
                        Delete.enable();
                    }
                },

                onDblClick: function () {
                    self.openUpdateDialog(self.$Grid.getSelectedData()[0].code);
                },

                onRefresh: this.refresh
            });

            this.$Grid.refresh();
        },

        /**
         * refresh the currency list
         */
        refresh: function () {
            return this.getCurrencies().then(function (list) {
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

                var perPage = this.$Grid.options.perPage,
                    page    = this.$Grid.options.page,
                    start   = (page - 1) * perPage,
                    total   = data.length;

                data = data.splice(start, perPage);

                this.$Grid.setData({
                    data : data,
                    total: total,
                    page : page
                });

                var buttons = this.$Grid.getButtons();

                var Edit = buttons.filter(function (Btn) {
                    return Btn.getAttribute('name') == 'edit';
                })[0];

                var Delete = buttons.filter(function (Btn) {
                    return Btn.getAttribute('name') == 'delete';
                })[0];

                Edit.disable();
                Delete.disable();

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
         * Delete a currency
         *
         * @param {String} currency
         * @returns {Promise}
         */
        deleteCurrency: function (currency) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_currency_ajax_delete', resolve, {
                    'package': 'quiqqer/currency',
                    currency : currency,
                    onError  : reject
                });
            });
        },

        /**
         * Create a new currency
         *
         * @param {String} currency
         * @returns {Promise}
         */
        createCurrency: function (currency) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_currency_ajax_create', resolve, {
                    'package': 'quiqqer/currency',
                    currency : currency,
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
        },

        /**
         * dialogs
         */

        /**
         * Opens the edit dialog
         *
         * @param {String} currency
         */
        openUpdateDialog: function (currency) {
            new CurrencyWindow({
                currency: currency,
                events  : {
                    onClose: this.refresh
                }
            }).open();
        },

        /**
         * Opens the delete dialog
         *
         * @param {String} currency
         */
        openDeleteDialog: function (currency) {
            var self = this;

            new QUIConfirm({
                icon       : 'fa fa-trash',
                texticon   : 'fa fa-trash',
                title      : QUILocale.get(lg, 'window.delete.title'),
                text       : QUILocale.get(lg, 'window.delete.text', {
                    currency: currency
                }),
                information: QUILocale.get(lg, 'window.delete.information', {
                    currency: currency
                }),
                maxHeight  : 400,
                maxWidth   : 600,
                autoclose  : false,
                events     : {
                    onSubmit: function (Win) {
                        Win.Loader.show();
                        self.deleteCurrency(currency).then(function () {

                            self.refresh().then(function () {
                                self.update();
                                Win.Loader.hide();
                                Win.close();
                            });

                        }, function () {
                            Win.Loader.hide();
                        });
                    }
                }
            }).open();
        },

        /**
         * Opens the create dialog
         */
        openCreateDialog: function () {
            var self = this;

            new QUIPrompt({
                icon       : 'fa fa-money',
                titleicon  : 'fa fa-money',
                title      : QUILocale.get(lg, 'window.create.title'),
                information: QUILocale.get(lg, 'window.create.information'),
                maxHeight  : 300,
                maxWidth   : 450,
                events     : {
                    onSubmit: function (value, Win) {
                        Win.Loader.show();

                        self.createCurrency(value).then(function () {
                            Win.close();
                            self.openUpdateDialog(value);
                        }, function () {
                            Win.Loader.hide();
                        });
                    }
                }
            }).open();
        }
    });
});
