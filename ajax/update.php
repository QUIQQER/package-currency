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
        $Currency = QUI\ERP\Currency\Handler::getCurrency($currency);

        $Currency->setExchangeRate($rate);
        $Currency->setCode($code);
        $Currency->save();
    },
    array('currency', 'code', 'rate'),
    'Permission::checkAdminUser'
);
