<?php

/**
 * This file contains package_quiqqer_currency_ajax_delete
 */

/**
 * Delete a currency
 *
 * @return array
 */
QUI::getAjax()->registerFunction(
    'package_quiqqer_currency_ajax_delete',
    function ($currencies) {
        $currencies = json_decode($currencies, true);

        foreach ($currencies as $currency) {
            QUI\ERP\Currency\Handler::deleteCurrency($currency);
        }
    },
    ['currencies'],
    'Permission::checkAdminUser'
);
