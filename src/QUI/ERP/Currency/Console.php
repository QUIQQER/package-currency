<?php

/**
 * This file contains QUI\ERP\Currency\Console
 */

namespace QUI\ERP\Currency;

use QUI;
use QUI\Exception;

/**
 * Import
 *
 * @author www.pcsg.de (Henning Leutz)
 */
class Console extends QUI\System\Console\Tool
{
    /**
     * Constructor for the CurrencyImporter class.
     *
     * Sets the name and description of the current command.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setName('currency:import')
            ->setDescription('Execute the import of the new currency exchange rates');
    }

    /**
     * (non-PHPdoc)
     *
     * @throws Exception
     * @see \QUI\System\Console\Tool::execute()
     */
    public function execute(): void
    {
        Import::import();
        QUI::getEvents()->fireEvent('quiqqerCurrencyImport');
    }
}
