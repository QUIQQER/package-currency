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
    public static function table(): string
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
    public static function createCurrency(string $currency, $rate = 1)
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
    public static function deleteCurrency(string $currency)
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
    public static function getDefaultCurrency(): ?Currency
    {
        if (is_null(self::$Default)) {
            try {
                $Config = QUI::getPackage('quiqqer/currency')->getConfig();

                self::$Default = self::getCurrency(
                    $Config->getValue('currency', 'defaultCurrency')
                );
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);

                self::$Default = self::getCurrency('EUR');
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
    public static function getUserCurrency($User = null): ?Currency
    {
        if ($User === null) {
            $User = QUI::getUserBySession();
        }

        if (!$User->getAttribute('quiqqer.erp.currency')) {
            $Currency = self::getUserCurrencyByCountry($User);

            if ($Currency) {
                return $Currency;
            }

            return null;
        }

        try {
            $currency = $User->getAttribute('quiqqer.erp.currency');
            $Currency = self::getCurrency($currency);

            return $Currency;
        } catch (\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
        }

        $Currency = self::getUserCurrencyByCountry($User);

        if ($Currency) {
            return $Currency;
        }

        return null;
    }

    /**
     * Return the currency of the user by its country
     *
     * @param null $User
     * @return Currency|null
     */
    public static function getUserCurrencyByCountry($User = null): ?Currency
    {
        if ($User === null) {
            $User = QUI::getUserBySession();
        }

        try {
            $Config  = QUI::getPackage('quiqqer/currency')->getConfig();
            $allowed = $Config->getValue('currency', 'allowedCurrencies');
            $allowed = explode(',', trim($allowed));
            $allowed = array_flip($allowed);

            $Country = $User->getCountry();

            if (!$Country) {
                return null;
            }

            $Currency = $Country->getCurrency();

            if (isset($allowed[$Currency->getCode()])) {
                return $Currency;
            }
        } catch (\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
        }

        return null;
    }

    /**
     * Return all allowed currencies
     *
     * @return Currency[] - [Currency, Currency, Currency]
     * @throws QUI\Exception
     */
    public static function getAllowedCurrencies(): array
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
    public static function getData(): array
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
     * @param string|Currency $currency
     * @return Currency
     * @throws QUI\Exception
     */
    public static function getCurrency($currency): Currency
    {
        $data = self::getData();
        $code = null;

        if (\is_string($currency)) {
            $code = $currency;
        } elseif (\is_array($currency) && isset($currency['code'])) {
            $code = $currency['code'];
        } elseif ($currency && \get_class($currency) == Currency::class) {
            return $currency;
        }

        if (isset($data[$code])) {
            return new Currency($data[$code]);
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
    public static function existCurrency(string $currency): bool
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
    public static function getCurrencies($Locale = false): array
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
                'text'       => $Locale->get('quiqqer/currency', 'currency.'.$currency.'.text'),
                'sign'       => $Locale->get('quiqqer/currency', 'currency.'.$currency.'.sign'),
                'code'       => $currency,
                'rate'       => $Currency->getExchangeRate(),
                'autoupdate' => $Currency->autoupdate()
            ];
        }

        return $result;
    }

    /**
     * @param $currency
     * @param $data
     *
     * @throws QUI\Database\Exception
     * @throws QUI\Exception
     */
    public static function updateCurrency($currency, $data)
    {
        QUI\Permissions\Permission::checkPermission('currency.edit');

        $Currency = self::getCurrency($currency);

        $dbData = [];

        if (isset($data['autoupdate'])) {
            $dbData['autoupdate'] = empty($data['autoupdate']) ? 0 : 1;
        }

        if (isset($data['code'])) {
            // set locale
            QUI\Translator::addUserVar(
                'quiqqer/currency',
                'currency.'.$Currency->getCode().'.sign',
                [
                    'en' => $data['code'],
                    'de' => $data['code']
                ]
            );
        }

        if (isset($data['rate'])) {
            $dbData['rate'] = floatval($data['rate']);
        }

        QUI::getDataBase()->update(
            Handler::table(),
            $dbData,
            ['currency' => $currency]
        );
    }
}
