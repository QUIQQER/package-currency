<?php

/**
 * This file contains \QUI\Currency
 */

namespace QUI;

use QUI;
use DOMDocument;

/**
 * Currency class
 * Conversion and currency sign
 *
 * @author  www.pcsg.de (Henning Leutz)
 * @package quiqqer/currency
 */
class Currency
{
    /**
     * currency temp list
     *
     * @var array
     */
    public static $currencies = array();

    /**
     * Return the real table name
     *
     * @return String
     */
    public static function Table()
    {
        return QUI_DB_PRFX . 'currency';
    }

    /**
     * Return the default currency
     *
     * @return string
     */
    public static function getDefaultCurrency()
    {
        return 'EUR';
    }

    /**
     * Calculation
     *
     * @param float $amount
     * @param String $currency_from
     * @param String $currency_to
     *
     * @return float
     * @throws QUI\Exception
     *
     * @example \QUI\Currency::calc( 1.45, 'USD', 'EUR' )
     * @example \QUI\Currency::calc( 1.45, 'USD' )
     */
    public static function calc($amount, $currency_from, $currency_to = 'EUR')
    {
        if ($currency_from === 'EUR' && $currency_to === 'EUR') {
            return $amount;
        }

        $signs = self::allCurrencies();

        if (!isset($signs[$currency_from]) && $currency_from !== 'EUR') {
            throw new QUI\Exception('Unknown currency: ' . $currency_from);
        }

        if (!isset($signs[$currency_to]) && $currency_to !== 'EUR') {
            throw new QUI\Exception('Unknown currency: ' . $currency_to);
        }

        $rate_from_to_euro = self::getRate($currency_from);

        // nach euro
        if ($currency_to === 'EUR') {
            return $amount * (1 / $rate_from_to_euro);
        }

        if ($currency_from === 'EUR') {
            return $amount * self::getRate($currency_to);
        }

        $eur = self::calc($amount, $currency_from);

        return $eur * self::getRate($currency_to);
    }

    /**
     * Calculation width Sign
     *
     * @param float $amount
     * @param String $currency_from
     * @param String $currency_to
     *
     * @return String
     */
    public static function calcWithSign($amount, $currency_from, $currency_to = 'EUR')
    {
        $amount = self::calc($amount, $currency_from, $currency_to);
        $sign   = self::getSign($currency_to);

        return $amount . ' ' . $sign;
    }

    /**
     * Format a currency value
     *
     * @param integer|float $amount
     * @param string $currency - currency code
     * @param string $locale - locale name
     *
     * @return String
     */
    public static function format($amount, $currency, $locale = 'de_DE')
    {
        if (strpos($locale, '.UTF-8') === false) {
            $locale = $locale . '.UTF-8';
        }

        setlocale(LC_MONETARY, $locale);

        $result = money_format(
            '%!n',
            QUI\Utils\StringHelper::parseFloat($amount)
        );

        $sign = self::getSign($currency);

        if ($locale === 'de_DE.UTF-8') {
            return $result . ' ' . $sign;
        }

        return $sign . ' ' . $result;
    }

    /**
     * Get the exchange rate
     *
     * @param String $currency
     *
     * @return float|false
     */
    public static function getRate($currency)
    {
        if (isset(self::$currencies[$currency])) {
            return self::$currencies[$currency];
        }

        $result = QUI::getDataBase()->fetch(array(
            'from' => self::Table(),
            'where' => array(
                'currency' => $currency
            )
        ));

        if (isset($result[0])) {
            self::$currencies[$currency] = (float)$result[0]['rate'];

            return (float)$result[0]['rate'];
        }

        return false;
    }

    /**
     * Return the exchange rate between two currencies
     *
     * @param String $currencyFrom
     * @param String $currencyTo
     *
     * @return float
     */
    public static function getRateFromTo($currencyFrom, $currencyTo)
    {
        $from = self::getRate($currencyFrom);
        $to   = self::getRate($currencyTo);

        if (!$from || !$to) {
            return false;
        }

        return round($from / $to, 8);
    }

    /**
     * Return the currency sign data (text and sign)
     *
     * @param string $currency - currency code
     *
     * @return array
     */
    public static function getSignData($currency)
    {
        $signs = self::allCurrencies();

        if (isset($signs[$currency])) {
            return $signs[$currency];
        }

        return array();
    }

    /**
     * return the currency sign
     *
     * @param string $currency - currency code (EUR or USD or JPY ...)
     *
     * @return string
     *
     * @example
     * echo \QUI\Currency::getSign('EUR');
     */
    public static function getSign($currency)
    {
        $signs = self::allCurrencies();

        if (!isset($signs[$currency])) {
            return $currency;
        }

        if (empty($signs[$currency]['sign'])) {
            return $currency;
        }

        return $signs[$currency]['sign'];
    }

    /**
     * Check if an exchange rate for the currency exists
     *
     * @param string $currency - currency name
     *
     * @return bool
     */
    public static function existCurrency($currency)
    {
        return self::getRate($currency) ? true : false;
    }

    /**
     * Get all currency entries
     *
     * @return array
     */
    public static function allCurrencies()
    {
        return array(
            'EUR' => array(
                'text' => 'Euro',
                'sign' => '&euro;'
            ),
            'USD' => array(
                'text' => 'US dollar',
                'sign' => '$'
            ),
            'JPY' => array(
                'text' => 'Japanese yen',
                'sign' => '&yen;'
            ),
            'BGN' => array(
                'text' => 'Bulgarian lev',
                'sign' => 'лв'
            ),
            'CZK' => array(
                'text' => 'Czech koruna',
                'sign' => 'Kč'
            ),
            'DKK' => array(
                'text' => 'Danish krone',
                'sign' => 'kr'
            ),
            'GBP' => array(
                'text' => 'Pound sterling',
                'sign' => '&pound;'
            ),
            'HUF' => array(
                'text' => 'Hungarian forint',
                'sign' => ''
            ),
            'LTL' => array(
                'text' => 'Lithuanian litas',
                'sign' => 'Lt'
            ),
            'LVL' => array(
                'text' => 'Latvian lats',
                'sign' => 'Ls'
            ),
            'PLN' => array(
                'text' => 'Polish zloty',
                'sign' => 'zł'
            ),
            'RON' => array(
                'text' => 'New Romanian',
                'sign' => 'RON'
            ),
            'SEK' => array(
                'text' => 'Swedish krona',
                'sign' => 'kr'
            ),
            'CHF' => array(
                'text' => 'Swiss franc',
                'sign' => ''
            ),
            'NOK' => array(
                'text' => 'Norwegian krone',
                'sign' => ''
            ),
            'HRK' => array(
                'text' => 'Croatian kuna',
                'sign' => 'kn'
            ),
            'RUB' => array(
                'text' => 'Russian rouble',
                'sign' => 'руб'
            ),
            'TRY' => array(
                'text' => 'Turkish lira',
                'sign' => '₤'
            ),
            'AUD' => array(
                'text' => 'Australian dollar',
                'sign' => 'A$'
            ),
            'BRL' => array(
                'text' => 'Brasilian real',
                'sign' => ''
            ),
            'CAD' => array(
                'text' => 'Canadian dollar',
                'sign' => 'C$'
            ),
            'CNY' => array(
                'text' => 'Chinese yuan renminbi',
                'sign' => ''
            ),
            'HKD' => array(
                'text' => 'Hong Kong dollar',
                'sign' => 'HK$'
            ),
            'IDR' => array(
                'text' => 'Indonesian rupiah',
                'sign' => ''
            ),
            'ILS' => array(
                'text' => 'Israeli shekel',
                'sign' => ''
            ),
            'INR' => array(
                'text' => 'Indian rupee',
                'sign' => ''
            ),
            'KRW' => array(
                'text' => 'South Korean won',
                'sign' => ''
            ),
            'MXN' => array(
                'text' => 'Mexican peso',
                'sign' => ''
            ),
            'MYR' => array(
                'text' => 'Malaysian ringgit',
                'sign' => ''
            ),
            'NZD' => array(
                'text' => 'New Zealand dollar',
                'sign' => 'NZ$'
            ),
            'PHP' => array(
                'text' => 'Philippine peso',
                'sign' => ''
            ),
            'SGD' => array(
                'text' => 'Singapore dollar',
                'sign' => 'S$'
            ),
            'THB' => array(
                'text' => 'Thai baht',
                'sign' => ''
            ),
            'ZAR' => array(
                'text' => 'South African rand',
                'sign' => ''
            ),
            'ISK' => array(
                'text' => 'Icelandic krona',
                'sign' => ''
            )
        );
    }

    /**
     * Import an XML File
     * eg: http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml
     *
     * @param String $xmlfile - Path to XML File
     */
    public static function import(
        $xmlfile = 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml'
    ) {
        $Dom = new DOMDocument();
        $Dom->load($xmlfile);

        $list = $Dom->getElementsByTagName('Cube');

        if (!$list->length) {
            return;
        }

        $values = array(
            'EUR' => '1.0'
        );

        for ($c = 0; $c < $list->length; $c++) {
            /* @var $Cube \DOMElement */
            $Cube = $list->item($c);

            $currency = $Cube->getAttribute('currency');
            $rate     = $Cube->getAttribute('rate');

            if (empty($currency)) {
                continue;
            }

            $values[$currency] = $rate;
        }

        $DataBase = QUI::getDataBase();

        foreach ($values as $currency => $rate) {
            $result = $DataBase->fetch(array(
                'from' => self::Table(),
                'where' => array(
                    'currency' => $currency
                )
            ));

            // Update
            if (isset($result[0])) {
                $DataBase->update(
                    self::Table(),
                    array('rate' => $rate),
                    array('currency' => $currency)
                );
            } else {
                $DataBase->insert(
                    self::Table(),
                    array(
                        'rate' => $rate,
                        'currency' => $currency
                    )
                );
            }
        }
    }
}
