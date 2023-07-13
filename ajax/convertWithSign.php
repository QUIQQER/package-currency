<?php

/**
 * This file contains package_quiqqer_currency_ajax_convertWithSign
 */

/**
 * Convert the amount list
 *
 * @return array
 */

QUI::$Ajax->registerFunction(
    'package_quiqqer_currency_ajax_convertWithSign',
    function ($data) {
        $data = json_decode($data, true);
        $result = [];

        foreach ($data as $entry) {
            $amount = $entry['amount'];
            $currencyFrom = $entry['from'];
            $currencyTo = $entry['to'];

            $result[] = [
                'amount' => $entry['amount'],
                'from' => $entry['from'],
                'to' => $entry['to'],
                'converted' => QUI\ERP\Currency\Calc::convertWithSign($amount, $currencyFrom, $currencyTo)
            ];
        }

        return $result;
    },
    ['data']
);
