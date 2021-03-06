<?php

/**
 * This file contains package_quiqqer_currency_ajax_convert
 */

/**
 * Convert an amount in another currency amount
 *
 * @return array
 */

QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_convert',
    function ($amount, $currencyFrom, $currencyTo) {
        return QUI\ERP\Currency\Calc::convert($amount, $currencyFrom, $currencyTo);
    },
    ['amount', 'currencyFrom', 'currencyTo']
);
