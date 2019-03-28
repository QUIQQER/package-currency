<?php

/**
 * This file contains package_quiqqer_currency_ajax_setUserCurrency
 */

/**
 * Set the user currency
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_setUserCurrency',
    function ($currency) {
        $allowed = QUI\ERP\Currency\Handler::getAllowedCurrencies();
        $allowed = \array_map(function ($Currency) {
            /* @var $Currency \QUI\ERP\Currency\Currency */
            return $Currency->getCode();
        }, $allowed);

        $allowed = \array_flip($allowed);

        if (!isset($allowed[$currency])) {
            return;
        }

        $User = QUI::getUserBySession();
        $User->setAttribute('quiqqer.erp.currency', $currency);
        $User->save();
    },
    ['currency']
);
