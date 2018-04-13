<?php

/**
 * This file contains package_quiqqer_currency_ajax_create
 */

/**
 * Returns all available currencies
 *
 * @return array
 */

QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_create',
    function ($currency) {
        QUI\ERP\Currency\Handler::createCurrency($currency);
    },
    ['currency'],
    'Permission::checkAdminUser'
);
