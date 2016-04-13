<?php

/**
 * This file contains package_quiqqer_currency_ajax_importFromECB
 */

/**
 * Imports the currencies from the ECB
 */

QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_importFromECB',
    function () {
        QUI\ERP\Currency\Import::importCurrenciesFromECB();
    },
    false,
    'Permission::checkAdminUser'
);
