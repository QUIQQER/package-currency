<?php

/**
 * This file contains \QUI\ERP\Currency\Cron
 */

namespace QUI\ERP\Currency;

use QUI;

/**
 * Class Calc
 * @package QUI\ERP\Currency
 */
class Cron
{
    /**
     * Start import
     */
    public static function import()
    {
        Import::import();
    }
}