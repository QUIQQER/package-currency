<?php

/**
 * Return all available currency types.
 *
 * @return array
 */

QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_getCurrencyTypes',
    function () {
        return QUI\ERP\Currency\Handler::getCurrencyTypes();
    },
    false,
    'Permission::checkAdminUser'
);
