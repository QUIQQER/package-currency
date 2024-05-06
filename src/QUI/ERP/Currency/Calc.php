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
     * @param float|int|string $amount
     * @param string|array|Currency $currencyFrom - based currency
     * @param array|string|Currency $currencyTo - optional, wanted currency, default = EUR
     * @return float
     *
     * @throws QUI\Exception
     */
    public static function convert(
        float|int|string $amount,
        Currency|array|string $currencyFrom,
        Currency|array|string $currencyTo = 'EUR'
    ): float {
        $From = Handler::getCurrency($currencyFrom);
        $To = Handler::getCurrency($currencyTo);

        return $From->convert($amount, $To);
    }

    /**
     * Convert with currency sign
     *
     * @param float|int|string $amount
     * @param array|string|Currency $currencyFrom - based currency
     * @param array|string|Currency $currencyTo - optional, wanted currency, default = EUR
     * @return string
     *
     * @throws QUI\Exception
     */
    public static function convertWithSign(
        float|int|string $amount,
        Currency|array|string $currencyFrom,
        Currency|array|string $currencyTo = 'EUR'
    ): string {
        $From = Handler::getCurrency($currencyFrom);
        $To = Handler::getCurrency($currencyTo);

        return $From->convertFormat($amount, $To);
    }

    /**
     * Return the exchange rate between two currencies
     *
     * @param array|string|Currency $currencyFrom
     * @param array|string|Currency $currencyTo
     * @return float|boolean
     *
     * @throws QUI\Exception
     */
    public static function getExchangeRateBetween(
        Currency|array|string $currencyFrom,
        Currency|array|string $currencyTo
    ): float|bool {
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
