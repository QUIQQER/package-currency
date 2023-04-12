<?php

/**
 * This file contains \QUI\ERP\Currency\Currency
 */

namespace QUI\ERP\Currency;

use NumberFormatter;
use QUI;

use function floatval;
use function is_string;
use function preg_replace;
use function round;
use function str_replace;
use function strpos;
use function trim;

/**
 * Currency class
 * Conversion and currency sign
 *
 * @author  www.pcsg.de (Henning Leutz)
 * @package quiqqer/currency
 */
class Currency extends AbstractCurrency
{
    /**
     * Get title of the type of this currency.
     *
     * @param QUI\Locale|null $Locale
     * @return string
     */
    public static function getCurrencyTypeTitle(?QUI\Locale $Locale = null): string
    {
        if (empty($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/currency', 'currencyType.default.title');
    }

    /**
     * Get internal identifier of the currency type.
     *
     * @return string
     */
    public static function getCurrencyType(): string
    {
        return Handler::CURRENCY_TYPE_DEFAULT;
    }
}
