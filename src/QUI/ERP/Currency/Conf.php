<?php

namespace QUI\ERP\Currency;

use QUI;

/**
 * Class Conf
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
        } catch (QUI\Exception $Exception) {
            return Handler::getDefaultCurrency();
        }
    }

    /**
     * Return currency conf
     *
     * @param $section
     * @param $key
     *
     * @return array|bool|string
     */
    public static function conf($section, $key)
    {
        try {
            $Package = QUI::getPackage('quiqqer/currency');
            $Config  = $Package->getConfig();

            return $Config->get($section, $key);
        } catch (QUI\Exception $Exception) {
        }

        return false;
    }
}
