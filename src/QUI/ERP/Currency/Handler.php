<?php

/**
 * This file contains \QUI\ERP\Currency\Handler
 */

namespace QUI\ERP\Currency;

use QUI;

/**
 * Currency class
 * Conversion and currency sign
 *
 * @author  www.pcsg.de (Henning Leutz)
 * @package quiqqer/currency
 */
class Handler
{
    /**
     * currency temp list
     *
     * @var array
     */
    protected static $currencies = array();

    /**
     * @var null
     */
    protected static $Default = null;

    /**
     * Return the real table name
     *
     * @return string
     */
    public static function table()
    {
        return QUI::getDBTableName('currency');
    }

    /**
     * Create a new currency
     *
     * @param string $currency - currency code
     * @param integer|float $rate - currency exchange rate, default = 1
     * @throws QUI\Exception
     */
    public static function createCurrency($currency, $rate = 1)
    {
        QUI\Permissions\Permission::checkPermission('currency.create');

        $Currency = null;

        try {
            $Currency = self::getCurrency($currency);
        } catch (QUI\Exception $Exception) {
        }

        if (!is_null($Currency)) {
            throw new QUI\Exception(array(
                'quiqqer/currency',
                'exception.already.exists',
                array('currency' => $currency)
            ));
        }

        if (!is_numeric($rate)) {
            throw new QUI\Exception(array(
                'quiqqer/currency',
                'exception.currency.rate.wrong.format'
            ));
        }

        QUI::getDataBase()->insert(self::table(), array(
            'currency' => $currency,
            'rate'     => (float)$rate
        ));

        // create translations
        $languageData = array(
            'datatype' => 'js,php'
        );

        $localeGroup = 'quiqqer/currency';
        $localeText  = 'currency.' . $currency . '.text';
        $localeSign  = 'currency.' . $currency . '.sign';

        $textData = QUI\Translator::getVarData($localeGroup, $localeText);
        $signData = QUI\Translator::getVarData($localeGroup, $localeSign);

        if (empty($textData)) {
            QUI\Translator::addUserVar(
                'quiqqer/currency',
                'currency.' . $currency . '.text',
                $languageData
            );
        }

        if (empty($signData)) {
            QUI\Translator::addUserVar(
                'quiqqer/currency',
                'currency.' . $currency . '.sign',
                $languageData
            );
        }
    }

    /**
     * Delete a currency
     *
     * @param string $currency - currency code
     * @throws QUI\Exception
     */
    public static function deleteCurrency($currency)
    {
        QUI\Permissions\Permission::checkPermission('currency.delete');

        QUI::getDataBase()->delete(self::table(), array(
            'currency' => $currency
        ));
    }

    /**
     * Return the default currency
     *
     * @return Currency
     */
    public static function getDefaultCurrency()
    {
        if (is_null(self::$Default)) {
            $Config = QUI::getPackage('quiqqer/currency')->getConfig();

            self::$Default = self::getCurrency(
                $Config->getValue('currency', 'defaultCurrency')
            );
        }

        return self::$Default;
    }

    /**
     * Return all allowed currencies
     *
     * @return array - [Currency, Currency, Currency]
     * @throws QUI\Exception
     */
    public static function getAllowedCurrencies()
    {
        $Config  = QUI::getPackage('quiqqer/currency')->getConfig();
        $allowed = $Config->getValue('currency', 'allowedCurrencies');

        $allowed = explode(',', trim($allowed));
        $list    = array();

        foreach ($allowed as $currency) {
            try {
                $list[] = self::getCurrency($currency);
            } catch (QUI\Exception $Exception) {
            }
        }

        return $list;
    }

    /**
     * Return the currency db data
     *
     * @return array
     */
    public static function getData()
    {
        if (!self::$currencies) {
            $data = QUI::getDataBase()->fetch(array(
                'from' => self::table()
            ));

            foreach ($data as $entry) {
                self::$currencies[$entry['currency']] = $entry;
            }
        }

        return self::$currencies;
    }

    /**
     * Return a currency
     *
     * @param string $currency
     * @return Currency
     * @throws QUI\Exception
     */
    public static function getCurrency($currency)
    {
        if (is_string($currency)) {
            return new Currency($currency);
        }

        if (get_class($currency) == Currency::class) {
            /* @var $currency Currency */
            return $currency;
        }

        throw new QUI\Exception(
            array(
                'quiqqer/currency',
                'currency.not.found'
            ),
            404
        );
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
        $data = self::getData();

        return isset($data[$currency]);
    }

    /**
     * Return all currency entries
     *
     * @param \QUI\Locale|boolean $Locale - optional, for translation
     * @return array
     */
    public static function getCurrencies($Locale = false)
    {
        if (!$Locale) {
            $Locale = QUI::getLocale();
        }

        $cacheName     = 'quiqqer/currency/list';
        $cacheNameLang = 'quiqqer/currency/list/' . $Locale->getCurrent();

        try {
            return QUI\Cache\Manager::get($cacheNameLang);
        } catch (QUI\Exception $Exception) {
        }

        try {
            $currencies = QUI\Cache\Manager::get($cacheName);
        } catch (QUI\Exception $Exception) {
            $currencies = array();
            $data       = self::getData();

            foreach ($data as $entry) {
                $currencies[] = $entry['currency'];
            }
        }

        $result = array();

        foreach ($currencies as $currency) {
            try {
                $Currency = self::getCurrency($currency);
            } catch (QUI\Exception $Exception) {
                continue;
            }

            $result[$currency] = array(
                'text'       => $Locale->get(
                    'quiqqer/currency',
                    'currency.' . $currency . '.text'
                ),
                'sign'       => $Locale->get(
                    'quiqqer/currency',
                    'currency.' . $currency . '.sign'
                ),
                'code'       => $currency,
                'rate'       => $Currency->getExchangeRate(),
                'autoupdate' => $Currency->autoupdate()
            );
        }

        return $result;
    }
}
