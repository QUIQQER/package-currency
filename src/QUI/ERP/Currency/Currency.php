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
     * @var float|bool
     */
    protected $exchangeRate = false;

    /**
     * @var int
     */
    protected $autoupdate = 1;

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

        if (isset($data[$currencyCode])) {
            $this->exchangeRate = (float)$data[$currencyCode]['rate'];
            $this->autoupdate   = $data[$currencyCode]['autoupdate'];
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
     * Return the currency data
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'text' => $this->getText(),
            'sign' => $this->getSign(),
            'code' => $this->getCode(),
            'rate' => $this->getExchangeRate(),
            'autoupdate' => $this->autoupdate()
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

        return $Formatter->formatCurrency($amount, $this->getCode());
    }

    /**
     * updates the currency itself?
     *
     * @return boolean
     */
    public function autoupdate()
    {
        return $this->autoupdate ? true : false;
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
        return $Currency->format(
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
        if ($Currency === false) {
            return $this->exchangeRate;
        }

        $Currency = Handler::getCurrency($Currency);
        $to       = $Currency->getExchangeRate();

        if (!$to) {
            return false;
        }

        return round($this->exchangeRate / $to, 8);
    }

    /**
     * Set the exchange rate
     * if you want to save it to the currency, use ->update()
     *
     * @param float|integer $rate
     * @throws QUI\Exception
     */
    public function setExchangeRate($rate)
    {
        QUI\Rights\Permission::checkPermission('currency.edit');

        if (!is_numeric($rate)) {
            throw new QUI\Exception(array());
        }

        $this->exchangeRate = (float)$rate;
    }

    /**
     * @param string $code
     * @throws QUI\Exception
     */
    public function setCode($code)
    {
        QUI\Rights\Permission::checkPermission('currency.edit');

        $this->code = $code;
    }

    /**
     * Set the autoupdate status
     *
     * @param bool $status
     */
    public function setAutoupdate($status)
    {
        QUI\Rights\Permission::checkPermission('currency.edit');

        $this->autoupdate = (bool)$status ? 1 : 0;
    }

    /**
     * alias for update()
     */
    public function save()
    {
        $this->update();
    }

    /**
     * Saves the currency
     */
    public function update()
    {
        QUI\Rights\Permission::checkPermission('currency.edit');

        QUI::getDataBase()->update(
            Handler::table(),
            array(
                'autoupdate' => $this->autoupdate() ? 1 : 0,
                'rate' => $this->getExchangeRate()
            ),
            array('currency' => $this->getCode())
        );
    }
}
