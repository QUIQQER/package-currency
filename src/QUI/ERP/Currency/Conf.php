<?php

namespace QUI\ERP\Currency;

use QUI;
use QUI\Exception;

/**
 * Class Conf
 *
 * The Conf class provides methods to retrieve configuration data related to currency
 *
 * @package YourPackage\Namespace
 */
class Conf
{
    /**
     * @return bool
     */
    public static function accountingCurrencyEnabled(): bool
    {
        return !!self::conf('currency', 'accountingCurrencyDiffers');
    }

    /**
     * Return the accounting currency
     * - if no accounting currency is set, the default currency will be returned
     *
     * @return Currency|null
     * @throws Exception
     */
    public static function getAccountingCurrency(): ?Currency
    {
        if (!self::accountingCurrencyEnabled()) {
            return Handler::getDefaultCurrency();
        }

        try {
            return Handler::getCurrency(
                self::conf('currency', 'accountingCurrency')
            );
        } catch (QUI\Exception) {
            return Handler::getDefaultCurrency();
        }
    }

    /**
     * Return currency conf
     *
     * @param string $section
     * @param string|null $key
     *
     * @return array|bool|string
     */
    public static function conf(string $section, ?string $key): bool|array|string
    {
        try {
            $Package = QUI::getPackage('quiqqer/currency');
            $Config = $Package->getConfig();

            return $Config->get($section, $key);
        } catch (QUI\Exception) {
        }

        return false;
    }
}
