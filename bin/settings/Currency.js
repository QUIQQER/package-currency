/**
 * @module package/quiqqer/currency/bin/settings/Currency
 * @author www.pcsg.de (Henning Leutz)
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

    const lg = 'quiqqer/currency';

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
            this.$Precision = null;

            this.$TranslationTitle = null;
            this.$TranslationSign = null;

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
                    currencyCodeDescription: QUILocale.get(lg, 'control.currency.code.decription'),
                    currencyPrecision      : QUILocale.get(lg, 'control.currency.precision')
                })
            });

            this.$Form = this.$Elm.getElement('form');

            this.$Code = this.$Form.elements.code;
            this.$Rate = this.$Form.elements.rate;
            this.$Precision = this.$Form.elements.precision;

            return this.$Elm;
        },

        /**
         * event: on inject
         */
        $onInject: function () {
            const TitleContainer = this.getElm().getElement('.currency-title'),
                  SignContainer  = this.getElm().getElement('.currency-sign'),
                  currency       = this.getAttribute('currency');

            this.$TranslationTitle = new Translation({
                'group'  : 'quiqqer/currency',
                'var'    : 'currency.' + currency + '.text',
                'package': 'quiqqer/currency'
            }).inject(TitleContainer);

            this.$TranslationSign = new Translation({
                'group'  : 'quiqqer/currency',
                'var'    : 'currency.' + currency + '.sign',
                'package': 'quiqqer/currency'
            }).inject(SignContainer);


            QUIAjax.get('package_quiqqer_currency_ajax_getCurrency', (data) => {
                this.$Code.value = data.code;
                this.$Rate.value = data.rate;
                this.$Precision.value = data.precision;
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
            return new Promise((resolve) => {
                Promise.all([
                    this.$TranslationTitle.save(),
                    this.$TranslationSign.save()
                ]).then(() => {
                    QUIAjax.post('package_quiqqer_currency_ajax_update', resolve, {
                        'package': 'quiqqer/currency',
                        currency : this.getAttribute('currency'),
                        code     : this.$Code.value,
                        rate     : this.$Rate.value,
                        precision: this.$Precision.value
                    });
                });
            });
        }
    });
});
