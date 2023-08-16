/**
 * @module package/quiqqer/currency/bin/settings/Currency
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/currency/bin/settings/Currency', [

    'qui/controls/Control',
    'qui/controls/loader/Loader',

    'Locale',
    'Ajax',
    'Mustache',

    'package/quiqqer/translator/bin/controls/Update',
    'package/quiqqer/currency/bin/Currency',
    'qui/utils/Form',

    'text!package/quiqqer/currency/bin/settings/Currency.html',
    'css!package/quiqqer/currency/bin/settings/Currency.css'

], function (QUIControl, QUILoader, QUILocale, QUIAjax, Mustache, Translation, Currencies, QUIFormUtils, template) {
    "use strict";

    const lg = 'quiqqer/currency';

    return new Class({

        Extends: QUIControl,
        Type: 'package/quiqqer/currency/bin/settings/Currency',

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

            this.Loader = new QUILoader();

            this.$TranslationTitle = null;
            this.$TranslationSign = null;
            this.$CurrencyTypeSelect = null;

            this.$ExtraSettingsContainer = null;

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
                html: Mustache.render(template, {
                    currencyTitle: QUILocale.get('quiqqer/system', 'title'),
                    currencyCode: QUILocale.get(lg, 'control.currency.code'),
                    currencySign: QUILocale.get(lg, 'control.currency.sign'),
                    currencyExchangeRate: QUILocale.get(lg, 'control.currency.rate'),
                    currencyCodeDescription: QUILocale.get(lg, 'control.currency.code.decription'),
                    currencyPrecision: QUILocale.get(lg, 'control.currency.precision'),
                    titleCurrencyType: QUILocale.get(lg, 'control.currency.titleCurrencyType'),
                    currencyType: QUILocale.get(lg, 'control.currency.currencyType')
                })
            });

            this.Loader.inject(this.$Elm);

            this.$Form = this.$Elm.getElement('form');

            this.$Code = this.$Form.elements.code;
            this.$Rate = this.$Form.elements.rate;
            this.$Precision = this.$Form.elements.precision;
            this.$CurrencyTypeSelect = this.$Form.elements.type;

            this.$ExtraSettingsContainer = this.$Elm.getElement('.quiqqer-currency-setting-extra');

            return this.$Elm;
        },

        /**
         * event: on inject
         */
        $onInject: function () {
            const TitleContainer = this.getElm().getElement('.currency-title'),
                SignContainer = this.getElm().getElement('.currency-sign'),
                currency = this.getAttribute('currency');

            this.$TranslationTitle = new Translation({
                'group': 'quiqqer/currency',
                'var': 'currency.' + currency + '.text',
                'package': 'quiqqer/currency'
            }).inject(TitleContainer);

            this.$TranslationSign = new Translation({
                'group': 'quiqqer/currency',
                'var': 'currency.' + currency + '.sign',
                'package': 'quiqqer/currency'
            }).inject(SignContainer);


            this.Loader.show();

            Promise.all([
                Currencies.getCurrency(this.getAttribute('currency'), true),
                Currencies.getCurrencyTypes()
            ]).then((result) => {
                const Currency = result[0];
                const currencyTypes = result[1];

                this.$Code.value = Currency.code;
                this.$Rate.value = Currency.rate;
                this.$Precision.value = Currency.precision;

                // Load select with currency types
                currencyTypes.forEach((CurrencyType) => {
                    new Element('option', {
                        value: CurrencyType.type,
                        html: CurrencyType.typeTitle
                    }).inject(this.$CurrencyTypeSelect);
                });

                const onCurrencyTypeChange = () => {
                    const currencyType = this.$CurrencyTypeSelect.value;

                    currencyTypes.forEach((CurrencyType) => {
                        if (CurrencyType.type !== currencyType) {
                            return;
                        }

                        if (CurrencyType.settingsFormHtml) {
                            this.$ExtraSettingsContainer.set('html', CurrencyType.settingsFormHtml);
                        } else {
                            this.$ExtraSettingsContainer.set('html', '');
                        }
                    });
                };

                this.$CurrencyTypeSelect.addEvent('change', onCurrencyTypeChange);

                this.$CurrencyTypeSelect.value = Currency.type;
                onCurrencyTypeChange();

                if (Currency.customData) {
                    QUIFormUtils.setDataToNode(Currency.customData, this.$ExtraSettingsContainer);
                }

                this.Loader.hide();
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
                        currency: this.getAttribute('currency'),
                        code: this.$Code.value,
                        rate: this.$Rate.value,
                        precision: this.$Precision.value,
                        type: this.$CurrencyTypeSelect.value,
                        customData: JSON.encode(QUIFormUtils.getDataFromNode(this.$ExtraSettingsContainer))
                    });
                });
            });
        }
    });
});
