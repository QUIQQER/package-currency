<?php

namespace QUI\ERP\Currency;

use Exception;
use QUI;
use QUI\Locale;

use function class_exists;
use function get_class;
use function in_array;
use function is_a;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function mb_strtolower;

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
     * Currency types.
     */
    const CURRENCY_TYPE_DEFAULT = 'default';

    /**
     * currency temp list
     *
     * @var array
     */
    protected static array $currencies = [];

    /**
     * @var Currency|null
     */
    protected static ?Currency $Default = null;

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
     * @param string $type (optional) - Currency type
     * @throws QUI\Exception
     */
    public static function createCurrency(string $currency, $rate = 1, string $type = self::CURRENCY_TYPE_DEFAULT)
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
            'rate' => (float)$rate,
            'type' => self::existsCurrencyType($type) ? $type : self::CURRENCY_TYPE_DEFAULT
        ]);

        // create translations
        $languageData = [
            'datatype' => 'js,php'
        ];

        $localeGroup = 'quiqqer/currency';
        $localeText = 'currency.' . $currency . '.text';
        $localeSign = 'currency.' . $currency . '.sign';

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
     * @throws QUI\Exception
     */
    public static function getDefaultCurrency(): ?Currency
    {
        if (self::$Default === null) {
            try {
                $Config = QUI::getPackage('quiqqer/currency')->getConfig();

                self::$Default = self::getCurrency(
                    $Config->getValue('currency', 'defaultCurrency')
                );
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::addError('Default currency is missing');

                try {
                    self::$Default = self::getCurrency('EUR');
                } catch (QUI\Exception $Exception) {
                    if ($Exception->getCode() === 404) {
                        // add EUR
                        self::createCurrency('EUR');
                        self::$Default = self::getCurrency('EUR');
                    }
                }
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
    public static function getUserCurrency(QUI\Interfaces\Users\User $User = null): ?Currency
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
            return self::getCurrency(
                $User->getAttribute('quiqqer.erp.currency')
            );
        } catch (Exception $Exception) {
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
            $Config = QUI::getPackage('quiqqer/currency')->getConfig();
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
        } catch (Exception $Exception) {
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
        $Config = QUI::getPackage('quiqqer/currency')->getConfig();
        $allowed = $Config->getValue('currency', 'allowedCurrencies');

        $allowed = explode(',', trim($allowed));
        $list = [];

        $default = self::getDefaultCurrency()->getCode();

        if (!in_array($default, $allowed)) {
            $allowed[] = $default;
        }

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
                $entry['type'] = mb_strtolower($entry['type']);

                if (!empty($entry['customData'])) {
                    $entry['customData'] = json_decode($entry['customData'], true);
                }

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

        if (is_string($currency)) {
            $code = $currency;
        } elseif (is_array($currency) && isset($currency['code'])) {
            $code = $currency['code'];
        } elseif (get_class($currency) == Currency::class) {
            return $currency;
        }

        if (isset($data[$code])) {
            $class = Currency::class;

            if (!empty($data[$code]['type'])) {
                $class = self::getCurrencyClassByType($data[$code]['type']);
            }

            return new $class($data[$code]);
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
     * @param Locale|boolean $Locale - optional, for translation
     * @return array
     */
    public static function getCurrencies($Locale = false): array
    {
        if (!$Locale) {
            $Locale = QUI::getLocale();
        }

        $cacheName = 'quiqqer/currency/list';
        $cacheNameLang = 'quiqqer/currency/list/' . $Locale->getCurrent();

        try {
            return QUI\Cache\Manager::get($cacheNameLang);
        } catch (QUI\Exception $Exception) {
        }

        try {
            $currencies = QUI\Cache\Manager::get($cacheName);
        } catch (QUI\Exception $Exception) {
            $currencies = [];
            $data = self::getData();

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

            $result[$currency] = $Currency->toArray();
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

        if (!empty($data['type']) && is_string($data['type']) && self::existsCurrencyType($data['type'])) {
            $dbData['type'] = $data['type'];
        }

        if (!empty($data['customData'])) {
            $dbData['customData'] = json_encode($data['customData']);
        }

        if (isset($data['code'])) {
            // set locale
            QUI\Translator::addUserVar(
                'quiqqer/currency',
                'currency.' . $Currency->getCode() . '.sign',
                [
                    'en' => $data['code'],
                    'de' => $data['code']
                ]
            );
        }

        if (isset($data['rate'])) {
            $dbData['rate'] = floatval($data['rate']);
        }

        if (isset($data['precision'])) {
            $dbData['precision'] = (int)$data['precision'];
        }

        QUI::getDataBase()->update(
            Handler::table(),
            $dbData,
            ['currency' => $currency]
        );

        QUI\Cache\Manager::clear('quiqqer/currency/list');
    }

    // region Currency types

    /**
     * @param string $type
     * @return string - Class path
     */
    protected static function getCurrencyClassByType(string $type): string
    {
        foreach (self::getCurrencyTypes() as $currencyType) {
            if ($currencyType['type'] === $type) {
                return $currencyType['class'];
            }
        }

        return Currency::class;
    }

    /**
     * @param string $type
     * @return bool
     */
    protected static function existsCurrencyType(string $type): bool
    {
        foreach (self::getCurrencyTypes() as $currencyType) {
            if ($currencyType['type'] === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get available currency types (provided by <currency> package.xml providers).
     *
     * @return array
     * [
     *     'type' => 'xyz',
     *     'typeTitle' => 'Currency Type XYZ',
     *     'class' => Class path,
     *     'settingsFormHtml' => string|null
     * ]
     */
    public static function getCurrencyTypes(): array
    {
        // @todo cache einbauen

        $packages = QUI::getPackageManager()->getInstalled();
        $currencyTypes = [];

        foreach ($packages as $installedPackage) {
            try {
                $Package = QUI::getPackage($installedPackage['name']);

                if (!$Package->isQuiqqerPackage()) {
                    continue;
                }

                $packageProvider = $Package->getProvider();

                if (empty($packageProvider['currency'])) {
                    continue;
                }

                foreach ($packageProvider['currency'] as $class) {
                    if (!class_exists($class)) {
                        continue;
                    }

                    if (!is_a($class, CurrencyInterface::class, true)) {
                        continue;
                    }

                    $currencyTypes[] = [
                        'type' => $class::getCurrencyType(),
                        'typeTitle' => $class::getCurrencyTypeTitle(),
                        'class' => $class,
                        'settingsFormHtml' => $class::getExtraSettingsFormHtml()
                    ];
                }
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        return $currencyTypes;
    }
    // endregion
}
