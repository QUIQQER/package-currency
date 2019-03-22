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
    protected static $currencies = [];

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
            throw new QUI\Exception([
                'quiqqer/currency',
                'exception.already.exists',
                ['currency' => $currency]
            ]);
        }

        if (!is_numeric($rate)) {
            throw new QUI\Exception([
                'quiqqer/currency',
                'exception.currency.rate.wrong.format'
            ]);
        }

        QUI::getDataBase()->insert(self::table(), [
            'currency' => $currency,
            'rate'     => (float)$rate
        ]);

        // create translations
        $languageData = [
            'datatype' => 'js,php'
        ];

        $localeGroup = 'quiqqer/currency';
        $localeText  = 'currency.'.$currency.'.text';
        $localeSign  = 'currency.'.$currency.'.sign';

        $textData = QUI\Translator::getVarData($localeGroup, $localeText);
        $signData = QUI\Translator::getVarData($localeGroup, $localeSign);

        if (empty($textData)) {
            QUI\Translator::addUserVar(
                'quiqqer/currency',
                'currency.'.$currency.'.text',
                $languageData
            );
        }

        if (empty($signData)) {
            QUI\Translator::addUserVar(
                'quiqqer/currency',
                'currency.'.$currency.'.sign',
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

        QUI::getDataBase()->delete(self::table(), [
            'currency' => $currency
        ]);
    }

    /**
     * Return the default currency
     *
     * @return Currency
     */
    public static function getDefaultCurrency()
    {
        if (is_null(self::$Default)) {
            try {
                $Config = QUI::getPackage('quiqqer/currency')->getConfig();

                self::$Default = self::getCurrency(
                    $Config->getValue('currency', 'defaultCurrency')
                );
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);

                self::$Default = new Currency('EUR');
            }
        }

        return self::$Default;
    }

    /**
     * Return the currency of the user
     * - This currency can be switched, so the user can get the prices in its currency
     *
     * @param null|QUI\Interfaces\Users\User $User - optional
     * @return Currency|null
     */
    public static function getUserCurrency($User = null)
    {
        if ($User === null) {
            $User = QUI::getUserBySession();
        }

        if (!$User->getAttribute('quiqqer.erp.currency')) {
            return null;
        }

        try {
            $currency = $User->getAttribute('quiqqer.erp.currency');
            $Currency = self::getCurrency($currency);

            return $Currency;
        } catch (\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
        }

        return null;
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
        $list    = [];

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
            try {
                $data = QUI::getDataBase()->fetch([
                    'from' => self::table()
                ]);
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::addError($Exception->getMessage());

                return [];
            }

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

        if (is_array($currency) && isset($currency['code'])) {
            return new Currency($currency['code']);
        }

        if ($currency && get_class($currency) == Currency::class) {
            /* @var $currency Currency */
            return $currency;
        }

        throw new QUI\Exception(
            ['quiqqer/currency', 'currency.not.found'],
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
        $cacheNameLang = 'quiqqer/currency/list/'.$Locale->getCurrent();

        try {
            return QUI\Cache\Manager::get($cacheNameLang);
        } catch (QUI\Exception $Exception) {
        }

        try {
            $currencies = QUI\Cache\Manager::get($cacheName);
        } catch (QUI\Exception $Exception) {
            $currencies = [];
            $data       = self::getData();

            foreach ($data as $entry) {
                $currencies[] = $entry['currency'];
            }
        }

        $result = [];

        foreach ($currencies as $currency) {
            try {
                $Currency = self::getCurrency($currency);
            } catch (QUI\Exception $Exception) {
                continue;
            }

            $result[$currency] = [
                'text'       => $Locale->get(
                    'quiqqer/currency',
                    'currency.'.$currency.'.text'
                ),
                'sign'       => $Locale->get(
                    'quiqqer/currency',
                    'currency.'.$currency.'.sign'
                ),
                'code'       => $currency,
                'rate'       => $Currency->getExchangeRate(),
                'autoupdate' => $Currency->autoupdate()
            ];
        }

        return $result;
    }
}
