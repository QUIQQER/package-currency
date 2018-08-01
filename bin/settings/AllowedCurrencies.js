/**
 * Allowed currencies
 * Control for currency administration
 *
 * @module package/quiqqer/currency/bin/settings/AllowedCurrencies
 * @author www.pcsg.de (Henning Leutz)
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

        Extends: QUIControl,
        Type   : 'package/quiqqer/currency/bin/settings/AllowedCurrencies',

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
                pagination       : true,
                multipleSelection: true,
                height           : 300,
                width            : width,
                columnModel      : [{
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
                buttons          : [{
                    name     : 'add',
                    text     : QUILocale.get(lg, 'grid.setting.button.add'),
                    textimage: 'fa fa-plus',
                    events   : {
                        onClick: this.openCreateDialog
                    }
                }, {
                    name     : 'edit',
                    text     : QUILocale.get(lg, 'grid.setting.button.edit'),
                    textimage: 'fa fa-edit',
                    disabled : true,
                    events   : {
                        onClick: function () {
                            self.openUpdateDialog(self.$Grid.getSelectedData()[0].code);
                        }
                    }
                }, {
                    type: 'separator'
                }, {
                    name     : 'delete',
                    text     : QUILocale.get(lg, 'grid.setting.button.delete'),
                    textimage: 'fa fa-trash',
                    disabled : true,
                    events   : {
                        onClick: function () {
                            var currencies = self.$Grid.getSelectedData().map(function (C) {
                                return C.code;
                            });

                            self.openDeleteDialog(currencies);
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
                        return Btn.getAttribute('name') === 'edit';
                    })[0];

                    var Delete = buttons.filter(function (Btn) {
                        return Btn.getAttribute('name') === 'delete';
                    })[0];

                    if (selected.length === 1) {
                        Edit.enable();
                        Delete.enable();
                    } else if (selected.length > 1) {
                        Edit.disable();
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

                    data.push(list[i]);
                }

                var perPage = this.$Grid.options.perPage,
                    page    = this.$Grid.options.page,
                    start   = (page - 1) * perPage,
                    total   = data.length;

                data = data.splice(start, perPage);

                data.each(function (entry, i) {
                    data[i].allowed = new QUISwitch({
                        status  : (typeof values[entry.code] !== 'undefined'),
                        currency: entry.code,
                        events  : {
                            onChange: this.$onCurrencyStatusChange
                        }
                    });

                    data[i].autoupdate = new QUISwitch({
                        status  : entry.autoupdate,
                        currency: entry.code,
                        events  : {
                            onChange: this.$changeAutoUpdate
                        }
                    });
                }.bind(this));


                this.$Grid.setData({
                    data : data,
                    total: total,
                    page : page
                });

                var buttons = this.$Grid.getButtons();

                var Edit = buttons.filter(function (Btn) {
                    return Btn.getAttribute('name') === 'edit';
                })[0];

                var Delete = buttons.filter(function (Btn) {
                    return Btn.getAttribute('name') === 'delete';
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
         * @param {Array|String} currencies
         * @returns {Promise}
         */
        deleteCurrency: function (currencies) {
            if (typeOf(currencies) === 'string') {
                currencies = [currencies];
            }

            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_currency_ajax_delete', resolve, {
                    'package' : 'quiqqer/currency',
                    currencies: JSON.encode(currencies),
                    onError   : reject
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
         *
         * @param {Object} Switch
         */
        $onCurrencyStatusChange: function (Switch) {
            var currency = Switch.getAttribute('currency');
            var values   = this.getAttribute('values');


            if (Switch.getStatus()) {
                values[currency] = 1;
            } else {
                if (currency in values) {
                    delete values[currency];
                }
            }

            this.setAttribute('values', values);
            this.update();

            var PanelNode = this.getElm().getParent('.qui-panel'),
                Panel     = QUI.Controls.getById(PanelNode.get('data-quiid'));

            if (!Panel) {
                return;
            }

            if (Panel.getType() !== 'controls/desktop/panels/XML') {
                return;
            }

            Panel.save();
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
         * @param {Array} currencies
         */
        openDeleteDialog: function (currencies) {
            if (!currencies.length) {
                return;
            }

            var self = this,
                text, information;

            if (currencies.length === 1) {
                text = QUILocale.get(lg, 'window.delete.text', {
                    currency: currencies[0]
                });

                information = QUILocale.get(lg, 'window.delete.information', {
                    currency: currencies[0]
                });
            } else {
                text = QUILocale.get(lg, 'window.delete.plural.text');

                information = QUILocale.get(lg, 'window.delete.plural.information', {
                    currencies: currencies.join(', ')
                });
            }

            new QUIConfirm({
                icon       : 'fa fa-trash',
                texticon   : 'fa fa-trash',
                title      : QUILocale.get(lg, 'window.delete.title'),
                text       : text,
                information: information,
                ok_button  : {
                    text     : QUILocale.get('quiqqer/system', 'delete'),
                    textimage: 'icon-ok fa fa-check'
                },
                maxHeight  : 400,
                maxWidth   : 600,
                autoclose  : false,
                events     : {
                    onSubmit: function (Win) {
                        Win.Loader.show();
                        self.deleteCurrency(currencies).then(function () {

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
