<?php

namespace QUITests\ERP\Currency;

use QUI;

/**
 * Class FieldsTest
 */
class HandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefaultCurrency()
    {
        $Currency = QUI\ERP\Currency\Handler::getDefaultCurrency();

        $this->assertNotEmpty($Currency->getText());
        $this->assertNotEmpty($Currency->getSign());
        $this->assertNotEmpty($Currency->getCode());
    }

    public function testGetData()
    {
        $data = QUI\ERP\Currency\Handler::getData();

        $this->assertNotEmpty($data);
    }

    public function testGetCurrency()
    {
        $EUR = QUI\ERP\Currency\Handler::getCurrency('EUR');
        $USD = QUI\ERP\Currency\Handler::getCurrency('USD');

        $this->assertEquals($EUR->getSign(), '€');
        $this->assertEquals($USD->getSign(), '$');

        $this->assertEquals($EUR->getCode(), 'EUR');
        $this->assertEquals($USD->getCode(), 'USD');
    }
}
