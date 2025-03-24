/**
 * @module package/quiqqer/payments/bin/backend/controls/search/Window
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/currency/bin/controls/search/Window', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'qui/controls/windows/Confirm',
    'package/quiqqer/currency/bin/controls/search/Search',
    'Locale'

], function (QUI, QUIControl, QUIButton, QUIConfirm, Search, QUILocale) {
    'use strict';

    return new Class({

        Extends: QUIConfirm,
        Type: 'package/quiqqer/currency/bin/controls/search/Window',

        Binds: [
            'search',
            'submit',
            '$onOpen',
            '$onResize',
            '$onSearch',
            '$onSearchBegin',
            'tableRefresh'
        ],

        options: {
            maxHeight: 600,
            maxWidth: 800,
            icon: 'fa fa-search',
            title: QUILocale.get('quiqqer/currency', 'window.search.title'),
            autoclose: true,
            multiple: false
        },

        initialize: function (options) {
            this.parent(options);

            this.$Search = null;

            this.addEvents({
                onOpen: this.$onOpen
            });
        },

        /**
         * event : on resize
         *
         * @return {Promise}
         */
        $onResize: function () {
            return this.$Search.resize();
        },

        /**
         * Return the DOMNode Element
         *
         * @returns {HTMLDivElement}
         */
        $onOpen: function (Win) {
            const self = this,
                Content = Win.getContent();

            Content.set('html', '');

            this.$Search = new Search({
                searchbutton: false,
                events: {
                    onDblClick: function () {
                        self.submit();
                    },

                    onSearchBegin: function () {
                        self.Loader.show();
                    },

                    onSearchEnd: function () {
                        self.Loader.hide();
                    }
                }
            }).inject(Content);

            this.$Search.resize();
        },

        /**
         * Execute the search
         */
        search: function () {
            this.$Search.search();
        },

        /**
         * Submit
         *
         * @fires onSubmit
         */
        submit: function () {
            let selected = this.$Search.getSelectedData();

            if (!selected.length) {
                return;
            }

            selected = selected.map((entry) => {
                return entry.code;
            });

            this.fireEvent('submit', [this, selected]);

            if (this.getAttribute('autoclose')) {
                this.close();
            }
        }
    });
});
