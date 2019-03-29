<?php

/**
 * This file contains package_quiqqer_currency_ajax_getDefault
 */

/**
 * Return the default currency
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_getDefault',
    function () {
        return QUI\ERP\Currency\Handler::getDefaultCurrency()->toArray();
    },
    false,
    'Permission::checkAdminUser'
);
