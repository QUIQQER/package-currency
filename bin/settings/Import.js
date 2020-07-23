/**
 * @module package/quiqqer/currency/bin/settings/Import
 * @author www.pcsg.de (Henning Leutz)
 */
define('package/quiqqer/currency/bin/settings/Import', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/buttons/Button',
    'qui/controls/windows/Confirm',
    'Locale',
    'Ajax'

], function (QUI, QUIControl, QUIButton, QUIConfirm, QUILocale, QUIAjax) {
    "use strict";

    var lg = 'quiqqer/currency';

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/currency/bin/settings/Import',

        initialize: function (options) {
            this.parent(options);

            this.$Input  = null;
            this.$Elm    = null;
            this.$Button = null;

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

            this.$Elm = new Element('div', {
                'class': 'quiqqer-currency-allowed',
                html   : '<div class="quiqqer-currency-import-container"></div>',
                styles : {
                    width: '100%'
                }
            }).wraps(this.$Input);

            new QUIButton({
                text  : QUILocale.get(lg, 'ecb.import.button.text'),
                events: {
                    onClick: this.openDialog
                },
                styles: {
                    width: '100%'
                }
            }).inject(this.$Elm);

            if (this.$Elm.getParent().hasClass('field-container') &&
                this.$Elm.getParent().getElement('.field-container-item')) {
                this.$Elm.getParent().getElement('.field-container-item').setStyle('display', 'none');
            }
        },

        /**
         * Open the ECB import
         */
        openDialog: function () {
            new QUIConfirm({
                icon       : 'fa fa-money',
                texticon   : 'fa fa-money',
                title      : QUILocale.get(lg, 'window.ecb.import.title'),
                text       : QUILocale.get(lg, 'window.ecb.import.text'),
                information: QUILocale.get(lg, 'window.ecb.import.information'),
                maxHeight  : 300,
                maxWidth   : 600,
                events     : {
                    onSubmit: function (Win) {
                        Win.Loader.show();
                        QUIAjax.post('package_quiqqer_currency_ajax_importFromECB', function () {
                            Win.close();

                            // refresh all package/quiqqer/currency/bin/settings/AllowedCurrencies
                            QUI.Controls.getByType(
                                'package/quiqqer/currency/bin/settings/AllowedCurrencies'
                            ).each(function (AllowedCurrencies) {
                                AllowedCurrencies.refresh();
                            });
                        }, {
                            'package': 'quiqqer/currency'
                        });
                    }
                }
            }).open();
        }
    });
});
