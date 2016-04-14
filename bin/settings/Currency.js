/**
 * @module package/quiqqer/currency/bin/settings/Currency
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require Locale
 * @require Ajax
 * @require Mustache
 * @require package/quiqqer/translator/bin/controls/Update
 * @require text!package/quiqqer/currency/bin/settings/Currency.html
 * @require css!package/quiqqer/currency/bin/settings/Currency.css
 */
define('package/quiqqer/currency/bin/settings/Currency', [

    'qui/QUI',
    'qui/controls/Control',
    'Locale',
    'Ajax',
    'Mustache',
    'package/quiqqer/translator/bin/controls/Update',

    'text!package/quiqqer/currency/bin/settings/Currency.html',
    'css!package/quiqqer/currency/bin/settings/Currency.css'

], function (QUI, QUIControl, QUILocale, QUIAjax, Mustache, Translation, template) {
    "use strict";

    var lg = 'quiqqer/currency';

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/currency/bin/settings/Currency',

        Binds: [
            '$onInject'
        ],

        options: {
            currency: false
        },

        initialize: function (options) {
            this.parent(options);

            this.$Form = null;
            this.$Code = null;
            this.$Rate = null;

            this.$TranslationTitle = null;
            this.$TranslationSign  = null;

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * Return the DOMNode
         *
         * @returns {HTMLDivElement}
         */
        create: function () {
            this.$Elm = new Element('div', {
                'class': 'quiqqer-currency-setting',
                html   : Mustache.render(template, {
                    currencyTitle          : QUILocale.get('quiqqer/system', 'title'),
                    currencyCode           : QUILocale.get(lg, 'control.currency.code'),
                    currencySign           : QUILocale.get(lg, 'control.currency.sign'),
                    currencyExchangeRate   : QUILocale.get(lg, 'control.currency.rate'),
                    currencyCodeDescription: QUILocale.get(lg, 'control.currency.code.decription')
                })
            });

            this.$Form = this.$Elm.getElement('form');

            this.$Code = this.$Form.elements.code;
            this.$Rate = this.$Form.elements.rate;

            return this.$Elm;
        },

        /**
         * event: on inject
         */
        $onInject: function () {
            var self = this;

            var TitleContainer = this.getElm().getElement('.currency-title'),
                SignContainer  = this.getElm().getElement('.currency-sign'),
                currency       = this.getAttribute('currency');

            this.$TranslationTitle = new Translation({
                'group': 'quiqqer/currency',
                'var'  : 'currency.' + currency + '.text'
            }).inject(TitleContainer);

            this.$TranslationSign = new Translation({
                'group': 'quiqqer/currency',
                'var'  : 'currency.' + currency + '.sign'
            }).inject(SignContainer);


            QUIAjax.get('package_quiqqer_currency_ajax_getCurrency', function (data) {
                self.$Code.value = data.code;
                self.$Rate.value = data.rate;
            }, {
                'package': 'quiqqer/currency',
                currency : this.getAttribute('currency')
            });
        },

        /**
         * alias for update()
         */
        save: function () {
            return this.update();
        },

        /**
         * Updates the currency
         *
         * @return {Promise}
         */
        update: function () {
            return new Promise(function (resolve) {

                Promise.all([
                    this.$TranslationTitle.save(),
                    this.$TranslationSign.save()
                ]).then(function () {

                    QUIAjax.post('package_quiqqer_currency_ajax_update', resolve, {
                        'package': 'quiqqer/currency',
                        currency : this.getAttribute('currency'),
                        code     : this.$Code.value,
                        rate     : this.$Rate.value
                    });

                }.bind(this));

            }.bind(this));
        }
    });
});
