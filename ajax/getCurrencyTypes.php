<?php

/**
 * Return all available currency types.
 *
 * @return array
 */

QUI::getAjax()->registerFunction(
    'package_quiqqer_currency_ajax_getCurrencyTypes',
    function () {
        return QUI\ERP\Currency\Handler::getCurrencyTypes();
    },
    false,
    'Permission::checkAdminUser'
);
