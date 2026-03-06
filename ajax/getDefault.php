<?php

/**
 * This file contains package_quiqqer_currency_ajax_getDefault
 */

/**
 * Return the default currency
 *
 * @return array
 */
QUI::getAjax()->registerFunction(
    'package_quiqqer_currency_ajax_getDefault',
    function () {
        $Default = QUI\ERP\Currency\Handler::getDefaultCurrency();
        return $Default instanceof QUI\ERP\Currency\Currency ? $Default->toArray() : [];
    },
    false,
    'Permission::checkAdminUser'
);
