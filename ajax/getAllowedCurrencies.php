<?php

/**
 * This file contains package_quiqqer_currency_ajax_getAllowedCurrencies
 */

/**
 * Returns the allowed currencies
 *
 * @return array
 */

QUI::$Ajax->registerFunction('package_quiqqer_currency_ajax_getAllowedCurrencies', function () {
    $allowed = QUI\ERP\Currency\Handler::getAllowedCurrencies();
    $result  = array();

    /* @var $Currency \QUI\ERP\Currency\Currency */
    foreach ($allowed as $Currency) {
        $result[] = $Currency->toArray();
    }

    return $result;
});
