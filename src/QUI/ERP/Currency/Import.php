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
     * Start import from
     * eg: http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml
     */
    public static function import()
    {
        $xmlfile = 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml';
        $Dom     = new \DOMDocument();
        $Dom->load($xmlfile);

        $list = $Dom->getElementsByTagName('Cube');

        if (!$list->length) {
            return;
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

        $DataBase = QUI::getDataBase();

        foreach ($values as $currency => $rate) {
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
}
