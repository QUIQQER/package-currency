<?php

/**
 * This file contains package_quiqqer_currency_getCurrencies
 */

/**
 * Returns all available currencies
 *
 * @return array
 */

QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_convertWithSign',
    function ($amount, $currencyFrom, $currencyTo) {
        $result = QUI\ERP\Currency\Calc::convertWithSign(
            $amount,
            $currencyFrom,
            $currencyTo
        );

        return $result;
    },
    array('amount', 'currencyFrom', 'currencyTo')
);
