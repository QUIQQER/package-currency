/**
 * @module package/quiqqer/currency/bin/backend/controls/search/Search
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/currency/bin/controls/search/Search', [

    'qui/controls/Control',
    'package/quiqqer/currency/bin/Currency',
    'qui/controls/buttons/Button',
    'qui/controls/buttons/Switch',
    'Locale',
    'Ajax',
    'controls/grid/Grid',

    'css!package/quiqqer/currency/bin/controls/search/Search.css'

], function (QUIControl, Currencies, QUIButton, QUISwitch, QUILocale, QUIAjax, Grid) {
    'use strict';

    const lg = 'quiqqer/currency';

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/currency/bin/controls/search/Search',

        Binds: [
            'search'
        ],

        options: {
            limit: 20,
            page: 1,
            search: false,
            onlyAllowed: true
        },

        initialize: function (options) {
            this.parent(options);

            this.$Container = null;
            this.$Grid = null;
            this.$Input = null;
        },

        /**
         * Return the DOMNode Element
         *
         * @returns {HTMLElement}
         */
        create: function () {
            this.$Elm = new Element('div', {
                'class': 'quiqqer-currency-search',
                html: '',
                styles: {
                    height: '100%',
                    width: '100%'
                }
            });

            this.$Input = this.$Elm.getElement('[type="search"]');

            if (this.getAttribute('search')) {
                this.$Input.value = this.getAttribute('search');
            }

            this.$Container = new Element('div');
            this.$Container.inject(this.$Elm);

            this.$Grid = new Grid(this.$Container, {
                columnModel: [
                    {
                        header: QUILocale.get(lg, 'grid.setting.currency'),
                        dataIndex: 'code',
                        dataType: 'string',
                        width: 60,
                        editable: true
                    },
                    {
                        header: QUILocale.get(lg, 'grid.setting.sign'),
                        dataIndex: 'sign',
                        dataType: 'string',
                        width: 60,
                        editable: true
                    },
                    {
                        header: QUILocale.get(lg, 'grid.setting.currency'),
                        dataIndex: 'title',
                        dataType: 'string',
                        width: 100
                    },
                    {
                        header: QUILocale.get(lg, 'grid.setting.typeTitle'),
                        dataIndex: 'typeTitle',
                        dataType: 'string',
                        width: 100
                    }
                ],
                pagination: false,
                filterInput: true,
                perPage: this.getAttribute('limit'),
                page: this.getAttribute('page'),
                sortOn: this.getAttribute('field'),
                serverSort: true,
                showHeader: true,
                sortHeader: true,
                alternaterows: true,
                resizeColumns: true,
                selectable: true,
                multipleSelection: true,
                resizeHeaderOnly: true
            });

            // Events
            this.$Grid.addEvents({
                onDblClick: () => {
                    this.fireEvent('dblClick', [this]);
                },
                onRefresh: this.search
            });

            this.$Grid.refresh();

            return this.$Elm;
        },

        /**
         * Resize
         *
         * @return {Promise}
         */
        resize: function () {
            const size = this.$Elm.getSize();

            return Promise.all([
                this.$Grid.setHeight(size.y),
                this.$Grid.setWidth(size.x)
            ]);
        },

        /**
         * execute the search
         */
        search: function () {
            this.fireEvent('searchBegin', [this]);

            return new Promise((resolve, reject) => {

                require(['package/quiqqer/currency/bin/settings/AllowedCurrencies'], (AllowedCurrencies) => {
                    let GetCurrencies;

                    if (this.getAttribute('onlyAllowed')) {
                        GetCurrencies = Currencies.getCurrencies();
                    } else {
                        GetCurrencies = new AllowedCurrencies().getCurrencies();
                    }

                    GetCurrencies.then((list) => {
                        let data = [];

                        for (let code in list) {
                            if (!list.hasOwnProperty(code)) {
                                continue;
                            }

                            data.push({
                                code: list[code].code,
                                sign: list[code].sign,
                                title: list[code].text,
                                typeTitle: list[code].typeTitle
                            });
                        }

                        this.$Grid.setData({
                            data: data
                        });

                        this.fireEvent('searchEnd', [this]);
                        resolve();
                    }).catch(reject);
                });
            });
        },

        /**
         * Return the selected user data
         *
         * @return {Array}
         */
        getSelectedData: function () {
            return this.$Grid.getSelectedData();
        }
    });
});
