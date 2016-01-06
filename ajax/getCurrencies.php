<?php

/**
 * This file contains package_quiqqer_currency_getCurrencies
 */

/**
 * Returns all available currencies
 *
 * @return array
 */

QUI::$Ajax->registerFunction('package_quiqqer_currency_ajax_getCurrencies', function () {
    return QUI\ERP\Currency\Handler::getCurrencies();
});
