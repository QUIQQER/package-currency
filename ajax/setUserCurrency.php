<?php

/**
 * This file contains package_quiqqer_currency_ajax_setUserCurrency
 */

/**
 * Set the user currency
 */

use QUI\ERP\Currency\Handler;

QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_setUserCurrency',
    function ($currency) {
        $allowed = Handler::getAllowedCurrencies();
        $allowed = array_map(function ($Currency) {
            return $Currency->getCode();
        }, $allowed);

        $allowed = array_flip($allowed);

        if (!isset($allowed[$currency])) {
            return;
        }

        $User = QUI::getUserBySession();
        $User->setAttribute('quiqqer.erp.currency', $currency);
        $User->save();

        Handler::setRuntimeCurrency(Handler::getCurrency($currency));
    },
    ['currency']
);
