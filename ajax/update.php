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
    function ($currency, $code, $rate, $precision, $type, $customData) {
        $customData = !empty($customData) ? json_decode($customData, true) : null;

        QUI\ERP\Currency\Handler::updateCurrency($currency, [
            'rate'       => $rate,
            'code'       => $code,
            'type'       => !empty($type) ? $type : \QUI\ERP\Currency\Handler::CURRENCY_TYPE_DEFAULT,
            'customData' => $customData
        ]);

        if (isset($precision)) {
            QUI\ERP\Currency\Handler::updateCurrency($currency, [
                'precision' => $precision
            ]);
        }
    },
    ['currency', 'code', 'rate', 'precision', 'type', 'customData'],
    'Permission::checkAdminUser'
);
