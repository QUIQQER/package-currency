<?php

/**
 * This file contains QUI\ERP\Currency\Console
 */

namespace QUI\ERP\Currency;

use QUI;

/**
 * Import
 *
 * @author www.pcsg.de (Henning Leutz)
 */
class Console extends QUI\System\Console\Tool
{
    /**
     * Konstruktor
     */
    public function __construct()
    {
        $this->setName('currency:import')
            ->setDescription('Execute the import of the new currency exchange rates');
    }

    /**
     * (non-PHPdoc)
     *
     * @see \QUI\System\Console\Tool::execute()
     */
    public function execute()
    {
        Import::import();

        QUI::getEvents()->fireEvent('quiqqerCurrencyImport');
    }
}
