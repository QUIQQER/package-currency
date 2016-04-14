<?php

/**
 * This file contains package_quiqqer_currency_ajax_delete
 */

/**
 * Delete a currency
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_delete',
    function ($currency) {
        QUI\ERP\Currency\Handler::deleteCurrency($currency);
    },
    array('currency'),
    'Permission::checkAdminUser'
);
