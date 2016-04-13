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
        $Currency = QUI\ERP\Currency\Handler::getCurrency($currency);
        $Currency->setAutoupdate($autoupdate);
        $Currency->save();
    },
    array('currency', 'autoupdate'),
    'Permission::checkAdminUser'
);
