<?php

/**
 * This file contains \QUI\ERP\Currency\Import
 */

namespace QUI\ERP\Currency;

use DOMDocument;
use DOMElement;
use Exception;
use QUI;

use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_SSL_VERIFYHOST;
use const CURLOPT_SSL_VERIFYPEER;

/**
 * Class Import
 * @package QUI\ERP\Currency
 */
class Import
{
    /**
     * Import all available currencies from the ECB
     *
     * @throws QUI\Exception
     */
    public static function importCurrenciesFromECB()
    {
        $values = self::getECBData();

        foreach ($values as $currency => $rate) {
            try {
                Handler::getCurrency($currency);
            } catch (QUI\Exception $Exception) {
                // currency not exists, we must create it
                Handler::createCurrency($currency, $rate);
            }
        }

        self::import();
    }

    /**
     * Start import from
     * updates the exchange rate
     *
     * from: http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml
     *
     * @throws QUI\Exception
     */
    public static function import()
    {
        $values = self::getECBData();

        // look if EUR is not the default currency
        $Default = Handler::getDefaultCurrency();

        if (!isset($values[$Default->getCode()])) {
            throw new QUI\Exception([
                'quiqqer/currency',
                'exception.could.not.import.currencies',
                ['currency' => $Default->getCode()]
            ]);
        }

        // is only used if EUR is not the default currency
        $baseRate = 1 / $values[$Default->getCode()]; // eq: 1EUR / 1$

        foreach ($values as $currency => $rate) {
            try {
                $Currency = Handler::getCurrency($currency);

                if ($Currency->autoupdate() === false) {
                    continue;
                }

                // is only used if EUR is not the default currency
                if ($Default->getCode() !== 'EUR') {
                    if ($Currency->getCode() === 'EUR') {
                        $rate = $baseRate;
                    } elseif ($Default->getCode() === $Currency->getCode()) {
                        $rate = 1;
                    } else {
                        // calc the rate
                        $rate = $rate * $baseRate;
                    }
                }

                Handler::updateCurrency($Currency->getCode(), [
                    'rate' => $rate
                ]);
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception, QUI\System\Log::LEVEL_WARNING);
            }
        }
    }

    /**
     * Fetch the daily currencies rates from the ECB
     *
     * @return array
     */
    protected static function getECBData(): array
    {
        $xmlFile = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

        try {
            $result = QUI\Utils\Request\Url::get($xmlFile, [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
        } catch (Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return [];
        }

        $Dom = new DOMDocument();
        $Dom->loadXML($result);

        $list = $Dom->getElementsByTagName('Cube');

        if (!$list->length) {
            return [];
        }

        $values = [
            'EUR' => '1.0'
        ];

        for ($c = 0; $c < $list->length; $c++) {
            /* @var $Cube DOMElement */
            $Cube = $list->item($c);

            $currency = $Cube->getAttribute('currency');
            $rate = $Cube->getAttribute('rate');

            if (empty($currency)) {
                continue;
            }

            $values[$currency] = $rate;
        }

        return $values;
    }
}
