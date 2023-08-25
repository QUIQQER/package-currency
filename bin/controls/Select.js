/**
 * Makes an input field to a currency selection field
 *
 * @module package/quiqqer/currency/bin/controls/Select
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onChange [ this ]
 */
define('package/quiqqer/currency/bin/controls/Select', [

    'qui/QUI',
    'qui/controls/elements/Select',
    'package/quiqqer/currency/bin/Currency',
    'Locale'

], function(QUI, QUIElementSelect, Currencies, QUILocale) {
    'use strict';

    const lg = 'quiqqer/currency';

    /**
     * @param {Object} options
     * @param {HTMLInputElement} [Input]  - (optional), if no input given, one would be created
     *
     * @memberof! <global>
     */
    return new Class({

        Extends: QUIElementSelect,
        Type: 'package/quiqqer/currency/bin/controls/Select',

        Binds: [
            '$onSearchButtonClick',
            'currencySearch'
        ],

        options: {},

        initialize: function(options) {
            this.parent(options);

            this.setAttribute('Search', this.currencySearch);
            this.setAttribute('icon', 'fa fa-money');
            this.setAttribute(
                'child',
                'package/quiqqer/currency/bin/controls/SelectItem'
            );

            this.setAttribute(
                'placeholder',
                QUILocale.get(lg, 'control.select.search.placeholder')
            );

            this.addEvents({
                onSearchButtonClick: this.$onSearchButtonClick
            });
        },

        /**
         * Search areas
         *
         * @param {String} value
         * @returns {Promise}
         */
        currencySearch: function(value) {
            return new Promise((resolve) => {
                require(['package/quiqqer/currency/bin/settings/AllowedCurrencies'], (AllowedCurrencies) => {
                    let GetCurrencies;

                    if (this.getAttribute('onlyAllowed')) {
                        GetCurrencies = Currencies.getCurrencies();
                    } else {
                        GetCurrencies = new AllowedCurrencies().getCurrencies();
                    }

                    GetCurrencies.then((list) => {
                        let result = [], data;
                        value = value.toLowerCase();


                        for (let currency in list) {
                            if (!list.hasOwnProperty(currency)) {
                                continue;
                            }

                            data = list[currency];
                            
                            if (data.text.toLowerCase().indexOf(value) !== -1) {
                                result.push({
                                    icon: 'fa fa-money',
                                    title: '#' + data.code + ' - <b>' + data.text + '</b>',
                                    id: data.code
                                });
                            }
                        }

                        resolve(result);
                    });
                });
            });
        },

        /**
         * event : on search button click
         *
         * @param self
         * @param Btn
         */
        $onSearchButtonClick: function(self, Btn) {
            Btn.setAttribute('icon', 'fa fa-spinner fa-spin');

            require([
                'package/quiqqer/currency/bin/controls/search/Window'
            ], function(Search) {
                new Search({
                    events: {
                        onSubmit: function(Win, values) {
                            for (let i = 0, len = values.length; i < len; i++) {
                                self.addItem(values[i]);
                            }
                        }
                    }
                }).open();

                Btn.setAttribute('icon', 'fa fa-search');
            });
        }
    });
});
