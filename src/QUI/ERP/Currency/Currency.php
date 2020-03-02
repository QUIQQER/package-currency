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
                ['quiqqer/currency', 'currency.not.found'],
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
     * Set the locale for the currency
     *
     * @param QUI\Locale $Locale
     */
    public function setLocale(QUI\Locale $Locale)
    {
        $this->Locale = $Locale;
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
            'currency.'.$this->getCode().'.text'
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
            'currency.'.$this->getCode().'.sign'
        );
    }

    /**
     * Return the currency data
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'text'       => $this->getText(),
            'sign'       => $this->getSign(),
            'code'       => $this->getCode(),
            'rate'       => $this->getExchangeRate(),
            'autoupdate' => $this->autoupdate()
        ];
    }

    /**
     * Return the float amount for the currency
     * example for the most currencies -> 0.11223 = 0.11
     *
     * @param float $amount
     * @param null $Locale -optional
     * @return float
     */
    public function amount($amount, $Locale = null)
    {
        if (!$Locale) {
            $Locale = $this->Locale;
        }

        $amount = $this->format($amount, $Locale);
        $amount = \preg_replace('/[^0-9,"."]/', '', $amount);
        $amount = \trim($amount);

        $decimalSeparator  = $Locale->getDecimalSeparator();
        $groupingSeparator = $Locale->getGroupingSeparator();

        if (\strpos($amount, $decimalSeparator) && $decimalSeparator != ' . ') {
            $amount = \str_replace($groupingSeparator, '', $amount);
        }

        $amount = \str_replace(',', '.', $amount);
        $amount = \floatval($amount);

        return $amount;
    }

    /**
     * Format an amount
     *
     * @param float $amount
     * @param null|QUI\Locale $Locale - optional, locale object
     * @return string
     */
    public function format($amount, $Locale = null)
    {
        if (!$Locale) {
            $Locale = $this->Locale;
        }

        $localeCode = $Locale->getLocalesByLang($Locale->getCurrent());

        $Formatter = new \NumberFormatter(
            $localeCode[0],
            \NumberFormatter::CURRENCY,
            $Locale->getAccountingCurrencyPattern()
        );

        if (\is_string($amount)) {
            $amount = \floatval($amount);
        }

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
        if (!is_numeric($amount)) {
            QUI\System\Log::addError('Only numeric are allowed Currency->convert()');

            $Exception = new QUI\Exception(
                QUI::getLocale()->get('quiqqer/quiqqer', 'exception.error')
            );

            QUI\System\Log::writeException($Exception);

            throw $Exception;
        }

        $Currency = Handler::getCurrency($Currency);
        $Default  = Handler::getDefaultCurrency();
        $default  = $Default->getCode();

        if ($this->getCode() == $Currency->getCode()) {
            return $amount;
        }

        $from = $this->getCode();
        $to   = $Currency->getCode();

        // exchange rates are based on the default currency (eq EUR)
        // $from == 'EUR' && $to != 'EUR'
        if ($from == $default && $to != $default) {
            return $amount * $Currency->getExchangeRate();
        }

        // $from != 'EUR' && $to == 'EUR'
        if ($from != $default && $to == $default) {
            return $amount * (1 / $this->getExchangeRate());
        }

        $currency = $this->convert($amount, $default);

        return $currency * $Currency->getExchangeRate();
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
        $Currency = Handler::getCurrency($Currency);

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

        try {
            $Currency = Handler::getCurrency($Currency);
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);

            return false;
        }

        $to = $Currency->getExchangeRate();

        if (!$to) {
            return false;
        }

        return \round($this->exchangeRate / $to, 8);
    }

    /**
     * Set the exchange rate
     * if you want to save it to the currency, use ->update()
     *
     * @param float|integer $rate
     *
     * @throws QUI\Exception
     * @throws QUI\Permissions\Exception
     */
    public function setExchangeRate($rate)
    {
        QUI\Permissions\Permission::checkPermission('currency.edit');

        if (!\is_numeric($rate)) {
            throw new QUI\Exception([
                'quiqqer/currency',
                'exception.currency.rate.wrong.format'
            ]);
        }

        $this->exchangeRate = (float)$rate;
    }

    /**
     * @param string $code
     *
     * @throws QUI\Permissions\Exception
     */
    public function setCode($code)
    {
        QUI\Permissions\Permission::checkPermission('currency.edit');

        $this->code = $code;
    }

    /**
     * Set the auto update status
     *
     * @param bool $status
     *
     * @throws QUI\Permissions\Exception
     */
    public function setAutoupdate($status)
    {
        QUI\Permissions\Permission::checkPermission('currency.edit');

        $this->autoupdate = (bool)$status ? 1 : 0;
    }

    /**
     * alias for update()
     *
     * @throws QUI\Permissions\Exception
     * @throws QUI\Exception
     */
    public function save()
    {
        $this->update();
    }

    /**
     * Saves the currency
     *
     * @throws QUI\Permissions\Exception
     * @throws QUI\Exception
     */
    public function update()
    {
        QUI\Permissions\Permission::checkPermission('currency.edit');

        QUI::getDataBase()->update(
            Handler::table(),
            [
                'autoupdate' => $this->autoupdate() ? 1 : 0,
                'rate'       => $this->getExchangeRate()
            ],
            ['currency' => $this->getCode()]
        );
    }
}
