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
    function ($currency, $code, $rate) {
        QUI\ERP\Currency\Handler::updateCurrency($currency, [
            'rate' => $rate,
            'code' => $code
        ]);
    },
    ['currency', 'code', 'rate'],
    'Permission::checkAdminUser'
);
