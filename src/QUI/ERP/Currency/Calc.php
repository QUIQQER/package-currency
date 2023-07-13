<?php

/**
 * This file contains \QUI\ERP\Currency\Calc
 */

namespace QUI\ERP\Currency;

use QUI;

/**
 * Class Calc
 *
 * @package QUI\ERP\Currency
 */
class Calc
{
    /**
     * Convert the amount from one to another currency
     *
     * @param float|string $amount
     * @param string|Currency $currencyFrom - based currency
     * @param string|Currency $currencyTo - optional, wanted currency, default = EUR
     * @return float
     *
     * @throws QUI\Exception
     */
    public static function convert($amount, $currencyFrom, $currencyTo = 'EUR'): float
    {
        $From = Handler::getCurrency($currencyFrom);
        $To = Handler::getCurrency($currencyTo);

        return $From->convert($amount, $To);
    }

    /**
     * Convert with currency sign
     *
     * @param float|string $amount
     * @param string|Currency $currencyFrom - based currency
     * @param string|Currency $currencyTo - optional, wanted currency, default = EUR
     *
     * @return string
     *
     * @throws QUI\Exception
     */
    public static function convertWithSign($amount, $currencyFrom, $currencyTo = 'EUR'): string
    {
        $From = Handler::getCurrency($currencyFrom);
        $To = Handler::getCurrency($currencyTo);

        return $From->convertFormat($amount, $To);
    }

    /**
     * Return the exchange rate between two currencies
     *
     * @param string|Currency $currencyFrom
     * @param string|Currency $currencyTo
     *
     * @return float|boolean
     *
     * @throws QUI\Exception
     */
    public static function getExchangeRateBetween($currencyFrom, $currencyTo)
    {
        $From = Handler::getCurrency($currencyFrom);
        $To = Handler::getCurrency($currencyTo);

        $from = $From->getExchangeRate();
        $to = $To->getExchangeRate();

        if (!$from || !$to) {
            return false;
        }

        return round($from / $to, 8);
    }
}
