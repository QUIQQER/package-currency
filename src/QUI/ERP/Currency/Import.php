<?php

/**
 * This file contains \QUI\ERP\Currency\Import
 */

namespace QUI\ERP\Currency;

use QUI;

/**
 * Class Import
 * @package QUI\ERP\Currency
 */
class Import
{
    /**
     * Import all available currencies from the ECB
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
    }

    /**
     * Start import from
     * updates the exchange rate
     *
     * from: http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml
     */
    public static function import()
    {
        $values = self::getECBData();

        foreach ($values as $currency => $rate) {
            try {
                $Currency = Handler::getCurrency($currency);

                if ($Currency->autoupdate() === false) {
                    continue;
                }

                $Currency->setExchangeRate($rate);
                $Currency->save();

            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception, QUI\System\Log::LEVEL_WARNING);
            }

            continue;
            $result = $DataBase->fetch(array(
                'from' => Handler::table(),
                'where' => array(
                    'currency' => $currency
                )
            ));

            // Update
            if (isset($result[0])) {
                $DataBase->update(
                    Handler::table(),
                    array('rate' => $rate),
                    array('currency' => $currency)
                );
            } else {
                $DataBase->insert(
                    Handler::table(),
                    array(
                        'rate' => $rate,
                        'currency' => $currency
                    )
                );
            }
        }
    }

    /**
     * Fetch the daily currencie rates from the ECB
     *
     * @return array|void
     */
    protected static function getECBData()
    {
        $xmlfile = 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml';
        $Dom     = new \DOMDocument();
        $Dom->load($xmlfile);

        $list = $Dom->getElementsByTagName('Cube');

        if (!$list->length) {
            return array();
        }

        $values = array(
            'EUR' => '1.0'
        );

        for ($c = 0; $c < $list->length; $c++) {
            /* @var $Cube \DOMElement */
            $Cube = $list->item($c);

            $currency = $Cube->getAttribute('currency');
            $rate     = $Cube->getAttribute('rate');

            if (empty($currency)) {
                continue;
            }

            $values[$currency] = $rate;
        }

        return $values;
    }
}
