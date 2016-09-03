/**
 * @module package/quiqqer/currency/bin/settings/Import
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/controls/buttons/Button
 * @require qui/controls/windows/Confirm
 * @require Locale
 * @require Ajax
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
                html   : '<div class="quiqqer-currency-import-container"></div>'
            }).wraps(this.$Input);

            new QUIButton({
                text  : QUILocale.get(lg, 'ecb.import.button.text'),
                events: {
                    onClick: this.openDialog
                }
            }).inject(this.$Elm);
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
                maxWidth   : 450,
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