<?php

/**
 * This file contains package_quiqqer_currency_ajax_convertWithSign
 */

/**
 * Convert the amount list
 *
 * @return array
 */

use QUI\ERP\Currency\Handler;

QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_convertWithSign',
    function ($data) {
        $data = json_decode($data, true);
        $result = [];

        if (!class_exists('QUI\ERP\Money\Price')) {
            return $result;
        }

        foreach ($data as $entry) {
            $amount = $entry['amount'];
            $currencyFrom = $entry['from'];
            $currencyTo = $entry['to'];
            $converted = QUI\ERP\Currency\Calc::convert($amount, $currencyFrom, $currencyTo);
            $convertedRound = $converted;

            $numberAsString = strval($converted);
            $exploded = explode('.', $numberAsString);
            $numberOfDecimalPlaces = isset($exploded[1]) ? strlen($exploded[1]) : 0;

            if (
                Handler::getCurrency($currencyTo)->getCurrencyType() !== Handler::CURRENCY_TYPE_DEFAULT
                && $numberOfDecimalPlaces > 4
            ) {
                $CurrencyTo = QUI\ERP\Currency\Handler::getCurrency($currencyTo);

                $priceRounded = round($converted, 4);
                $PriceDisplay = new QUI\ERP\Money\Price($priceRounded, $CurrencyTo);
                $convertedRound = '~' . $PriceDisplay->getDisplayPrice();
            } else {
                $convertedRound = QUI\ERP\Currency\Calc::convertWithSign($convertedRound, $currencyFrom, $currencyTo);
            }

            $result[] = [
                'amount' => $entry['amount'],
                'from' => $entry['from'],
                'to' => $entry['to'],
                'converted' => QUI\ERP\Currency\Calc::convertWithSign($amount, $currencyFrom, $currencyTo),
                'convertedRound' => $convertedRound,
                'id' => $entry['id']
            ];
        }

        return $result;
    },
    ['data']
);
