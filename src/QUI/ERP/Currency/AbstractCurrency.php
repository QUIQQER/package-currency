<?php

/**
 * This file contains \QUI\ERP\Currency\Currency
 */

namespace QUI\ERP\Currency;

use NumberFormatter;
use QUI;

use QUI\Exception;

use function array_key_exists;
use function floatval;
use function is_array;
use function is_float;
use function is_int;
use function is_numeric;
use function is_string;
use function preg_replace;
use function round;
use function str_replace;
use function strpos;
use function trim;

/**
 * Currency AbstractCurrency
 *
 * Base class for all classes implementing CurrencyInterface
 *
 * @author www.pcsg.de (Henning Leutz)
 * @author www.pcsg.de (Patrick Müller)
 */
abstract class AbstractCurrency implements CurrencyInterface
{
    /**
     * @var string
     */
    protected string $code;

    /**
     * @var integer
     */
    protected int $precision = 2;

    /**
     * @var float|bool
     */
    protected bool|float $exchangeRate = false;

    /**
     * @var int
     */
    protected mixed $autoupdate = 1;

    /**
     * @var ?QUI\Locale
     */
    protected ?QUI\Locale $Locale;

    protected array $customData = [];

    /**
     * Currency constructor.
     *
     * @param array $data
     * @param boolean|QUI\Locale $Locale - Locale for the currency
     *
     * @throws QUI\Exception
     */
    public function __construct(array $data, $Locale = false)
    {
        if (!isset($data['currency']) && isset($data['code'])) {
            $data['currency'] = $data['code'];
        }

        if (!isset($data['currency'])) {
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

        $this->code = $data['currency'];
        $this->exchangeRate = (float)$data['rate'];
        $this->autoupdate = $data['autoupdate'];

        if (isset($data['precision'])) {
            $this->precision = $data['precision'];
        }

        if (!empty($data['customData']) && is_array($data['customData'])) {
            $this->customData = $data['customData'];
        }
    }

    /**
     * Set the locale for the currency
     *
     * @param QUI\Locale $Locale
     */
    public function setLocale(QUI\Locale $Locale): void
    {
        $this->Locale = $Locale;
    }

    /**
     * Return the currency code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Return the currency text
     *
     * @return string
     */
    public function getText(): string
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
    public function getSign(): string
    {
        return QUI::getLocale()->get(
            'quiqqer/currency',
            'currency.' . $this->getCode() . '.sign'
        );
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * Return the currency data
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'text' => $this->getText(),
            'sign' => $this->getSign(),
            'code' => $this->getCode(),
            'rate' => $this->getExchangeRate(),
            'autoupdate' => $this->autoupdate(),
            'precision' => $this->getPrecision(),
            'type' => $this::getCurrencyType(),
            'typeTitle' => $this::getCurrencyTypeTitle(),
            'customData' => $this->getCustomData()
        ];
    }

    /**
     * Return the float amount for the currency
     * example for the most currencies -> 0.11223 = 0.11
     *
     * @param float|int|string $amount
     * @param null|QUI\Locale $Locale -optional
     * @return float
     */
    public function amount($amount, QUI\Locale $Locale = null): float
    {
        if (is_float($amount) || is_int($amount)) {
            return $amount;
        }

        if (!$Locale) {
            $Locale = $this->Locale;
        }

        $amount = $this->format($amount, $Locale);
        $amount = preg_replace('/[^0-9,".]/', '', $amount);
        $amount = trim($amount);

        $decimalSeparator = $Locale->getDecimalSeparator();
        $groupingSeparator = $Locale->getGroupingSeparator();

        if (strpos($amount, $decimalSeparator) && $decimalSeparator != ' . ') {
            $amount = str_replace($groupingSeparator, '', $amount);
        }

        $amount = str_replace(',', '.', $amount);

        return floatval($amount);
    }

    /**
     * Format an amount
     *
     * @param float|string $amount
     * @param null|QUI\Locale $Locale - optional, locale object
     * @return string
     */
    public function format($amount, QUI\Locale $Locale = null): string
    {
        if (!$Locale) {
            $Locale = $this->Locale;
        }

        $localeCode = $Locale->getLocalesByLang($Locale->getCurrent());

        $Formatter = new NumberFormatter(
            $localeCode[0],
            NumberFormatter::CURRENCY,
            $Locale->getAccountingCurrencyPattern()
        );

        $Formatter->setPattern($Locale->getAccountingCurrencyPattern());
        $Formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $this->precision);

        if (is_string($amount)) {
            $amount = floatval($amount);
        }

        if (empty($amount)) {
            $amount = 0;
        }

        // double precision bug
        if ($this->precision > 2) {
            // 0.93134354524138 MEM wurde durch den formatter zu 0.9313435452413777 MEM angezeigt
            // problem:double precision
            $amount = (string)$amount;
            $amount = floatval($amount);
        }

        $code = $this->getCode();

        if (mb_strlen($code) <= 3) {
            return $Formatter->formatCurrency($amount, $this->getCode());
        }

        $replacer = 'ZZZ';
        $result = $Formatter->formatCurrency($amount, $replacer);

        return str_replace($replacer, $this->getCode(), $result);
    }

    /**
     * updates the currency itself?
     *
     * @return boolean
     */
    public function autoupdate(): bool
    {
        return (bool)$this->autoupdate;
    }

    /**
     * Convert the amount to the wanted currency
     *
     * @param float|string $amount
     * @param string|CurrencyInterface $Currency
     * @return float|int|string
     *
     * @throws Exception
     */
    public function convert($amount, $Currency): float|int|string
    {
        if (!is_numeric($amount)) {
            QUI\System\Log::addError('Only numeric are allowed Currency->convert()', [
                '$amount' => $amount
            ]);

            $Exception = new QUI\Exception(
                QUI::getLocale()->get('quiqqer/quiqqer', 'exception.error')
            );

            QUI\System\Log::writeException($Exception);

            throw $Exception;
        }

        $Currency = Handler::getCurrency($Currency);
        $Default = Handler::getDefaultCurrency();
        $default = $Default->getCode();

        if ($this->getCode() == $Currency->getCode()) {
            return $amount;
        }

        $from = $this->getCode();
        $to = $Currency->getCode();

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
     * @param float|string $amount
     * @param string|Currency $Currency
     * @return string
     *
     * @throws QUI\Exception
     */
    public function convertFormat($amount, $Currency): string
    {
        if (!($Currency instanceof Currency)) {
            $Currency = Handler::getCurrency($Currency);
        }

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
    public function getExchangeRate($Currency = false): float|bool
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

        return round($this->exchangeRate / $to, 8);
    }

    /**
     * Set the exchange rate
     * if you want to save it to the currency, use ->update()
     *
     * @param float|integer $rate
     */
    public function setExchangeRate($rate): void
    {
        $this->exchangeRate = (float)$rate;
    }

    /**
     * If the currency type has extra settings, use this form to set them.
     *
     * @return string|null
     */
    public static function getExtraSettingsFormHtml(): ?string
    {
        return null;
    }

    /**
     * Get all custom data.
     *
     * @return array
     */
    public function getCustomData(): array
    {
        return $this->customData;
    }

    /**
     * Set specific custom data entry.
     *
     * @param string $key
     * @param $value
     * @return void
     */
    public function setCustomDataEntry(string $key, $value): void
    {
        $this->customData[$key] = $value;
    }

    /**
     * Get specific custom data entry.
     *
     * @param string $key
     * @return mixed
     */
    public function getCustomDataEntry(string $key): mixed
    {
        if (array_key_exists($key, $this->customData)) {
            return $this->customData[$key];
        }

        return null;
    }
}
