<?php

/**
 * This file contains package_quiqqer_currency_ajax_getCurrency
 */

/**
 * Return the currency
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_getCurrency',
    function ($currency) {
        $Currency = QUI\ERP\Currency\Handler::getCurrency($currency);

        return $Currency->toArray();
    },
    array('currency')
);
