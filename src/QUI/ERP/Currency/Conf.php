<?php

namespace QUI\ERP\Currency;

use QUI;

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
     * @return Currency|null
     */
    public static function getAccountingCurrency(): ?Currency
    {
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
