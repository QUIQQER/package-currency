<?php

namespace QUITests\ERP\Currency;

use QUI;

/**
 * Class FieldsTest
 */
class ImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Create child test
     * @throws \QUI\Exception
     */
    public function testImport()
    {
        QUI\ERP\Currency\Import::importCurrenciesFromECB();

        $result = QUI::getDataBase()->fetch(array(
            'from' => QUI\ERP\Currency\Handler::table()
        ));

        $this->assertNotEmpty($result);
    }
}
