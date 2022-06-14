<?php

/**
 * This file contains package_quiqqer_currency_ajax_update
 */

/**
 * Saves a currency
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_update',
    function ($currency, $code, $rate, $precision) {
        QUI\ERP\Currency\Handler::updateCurrency($currency, [
            'rate' => $rate,
            'code' => $code
        ]);

        if (isset($precision)) {
            QUI\ERP\Currency\Handler::updateCurrency($currency, [
                'precision' => $precision
            ]);
        }
    },
    ['currency', 'code', 'rate', 'precision'],
    'Permission::checkAdminUser'
);
