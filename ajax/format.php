<?php

/**
 * This file contains package_quiqqer_currency_ajax_format
 */

/**
 * Return the formatted currency
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_format',
    function ($currency, $amount) {

        $Currency = QUI\ERP\Currency\Handler::getCurrency($currency);

        return $Currency->format($amount);
    },
    array('currency', 'amount')
);
