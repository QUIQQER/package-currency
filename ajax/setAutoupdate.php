<?php

/**
 * This file contains package_quiqqer_currency_ajax_setAutoupdate
 */

/**
 * Returns all available currencies
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_setAutoupdate',
    function ($currency, $autoupdate) {
        QUI\ERP\Currency\Handler::updateCurrency($currency, [
            'autoupdate' => $autoupdate
        ]);
    },
    ['currency', 'autoupdate'],
    'Permission::checkAdminUser'
);
