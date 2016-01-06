<?php

/**
 * This file contains \QUI\ERP\Currency\Currency
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
class Currency
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var int
     */
    protected $exchangeRate = false;

    /**
     * @var QUI\Locale
     */
    protected $Locale;

    /**
     * Currency constructor.
     *
     * @param string $currencyCode - Currency Code eq: EUR
     * @param boolean|QUI\Locale $Locale - Locale for the currency
     *
     * @throws QUI\Exception
     */
    public function __construct($currencyCode, $Locale = false)
    {
        if (!Handler::existCurrency($currencyCode)) {
            throw new QUI\Exception(
                array(
                    'quiqqer/currency',
                    'currency.not.found'
                ),
                404
            );
        }

        if (!$Locale) {
            $this->Locale = QUI::getLocale();
        } else {
            $this->Locale = $Locale;
        }

        $this->code = $currencyCode;

        $data = Handler::getData();

        if (isset($data[$this->getCode()])) {
            $this->exchangeRate = $this->getCode();
        }
    }

    /**
     * Return the currency code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Return the currency text
     *
     * @return string
     */
    public function getText()
    {
        return QUI::getLocale()->get(
            'quiqqer/currency',
            'currency.' . $this->getCode() . '.text'
        );
    }

    /**
     * Return the currency text
     *
     * @return string
     */
    public function getSign()
    {
        return QUI::getLocale()->get(
            'quiqqer/currency',
            'currency.' . $this->getCode() . '.sign'
        );
    }

    /**
     * Format an amount
     *
     * @param float $amount
     * @return string
     */
    public function format($amount)
    {
        $localeCode = $this->Locale->getLocalesByLang(
            $this->Locale->getCurrent()
        );

        $Formatter = new \NumberFormatter(
            $localeCode[0],
            \NumberFormatter::CURRENCY
        );

        return $Formatter->format($amount);
    }

    /**
     * Convert the amount to the wanted currency
     *
     * @param float $amount
     * @param string|Currency $Currency
     * @return float
     *
     * @throws QUI\Exception
     */
    public function convert($amount, $Currency)
    {
        $Currency = Handler::getCurrency($Currency);

        if ($this->getCode() == $Currency->getCode()) {
            return $amount;
        }

        $from = $this->getCode();
        $to   = $Currency->getCode();

        // exchange rates are based at EUR
        if ($from == 'EUR' && $to != 'EUR') {
            return $amount * $Currency->getExchangeRate();
        }

        if ($from != 'EUR' && $to == 'EUR') {
            return $amount * (1 / $this->getExchangeRate());
        }

        $eur = $this->convert($amount, 'EUR');

        return $eur * $Currency->getExchangeRate();
    }

    /**
     *
     * @param float $amount
     * @param string|Currency $Currency
     * @return string
     *
     * @throws QUI\Exception
     */
    public function convertFormat($amount, $Currency)
    {
        return $this->format(
            $this->convert($amount, $Currency)
        );
    }

    /**
     * Return the exchange rate to the EUR
     *
     * @param boolean|string|Currency $Currency - optional, default = false -> return own exchange rate
     * @return float|boolean
     */
    public function getExchangeRate($Currency = false)
    {
        $data = Handler::getData();

        if (!isset($data[$this->getCode()])) {
            return false;
        }

        $ownRate = $data[$this->getCode()]['rate'];

        if ($Currency === false) {
            return (float)$ownRate;
        }

        $Currency = Handler::getCurrency($Currency);
        $to       = $Currency->getExchangeRate();

        if (!$to) {
            return false;
        }

        return round($ownRate / $to, 8);
    }
}
