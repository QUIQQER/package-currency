<?php

namespace QUITests\ERP\Currency;

use QUI;

/**
 * Class FieldsTest
 */
class CurrencyTest extends \PHPUnit_Framework_TestCase
{
    public function testCurrency()
    {
        // get EUR
        $EUR = new QUI\ERP\Currency\Currency('EUR');

        $this->assertNotEmpty($EUR);
        $this->assertNotEmpty($EUR->getSign());
        $this->assertNotEmpty($EUR->getExchangeRate());
        $this->assertNotEmpty($EUR->getCode());
        $this->assertNotEmpty($EUR->getText());
    }

    public function testUnknownCurrency()
    {
        // test unknown
        $this->setExpectedException('QUI\Exception');

        new QUI\ERP\Currency\Currency('NaN');
    }

    public function testGetCode()
    {
        $EUR = new QUI\ERP\Currency\Currency('EUR');

        $this->assertEquals($EUR->getCode(), 'EUR');
    }

    public function testGetSign()
    {
        $EUR = new QUI\ERP\Currency\Currency('EUR');

        $this->assertEquals($EUR->getSign(), '€');
    }

    public function testGetText()
    {
        $EUR = new QUI\ERP\Currency\Currency('EUR');

        $this->assertNotEmpty($EUR->getText());
    }

    public function testFormat()
    {
        $EUR    = new QUI\ERP\Currency\Currency('EUR');
        $format = $EUR->format(14.99);

        $this->assertTrue(is_string($format));
    }

    public function testGetExchangeRate()
    {
        $EUR = new QUI\ERP\Currency\Currency('EUR');
        $USD = new QUI\ERP\Currency\Currency('USD');

        $eurRate = $EUR->getExchangeRate();
        $usdRate = $EUR->getExchangeRate($USD);

        $this->assertTrue(is_float($eurRate));

        // to another currency
        $this->assertTrue(is_float($usdRate));
        $this->assertNotEquals($eurRate, $usdRate);
    }

    public function testConvert()
    {
        $EUR = new QUI\ERP\Currency\Currency('EUR');
        $USD = new QUI\ERP\Currency\Currency('USD');
        $GBP = new QUI\ERP\Currency\Currency('GBP');

        $usd = $EUR->convert(1, 'USD');

        $this->assertNotEquals($usd, $EUR->getExchangeRate());
        $this->assertTrue(is_float($usd));


        // some calc tests
        $oneDollarInEuro = $USD->convert(1, 'EUR');
        $onePoundInEuro  = $GBP->convert(1, 'EUR');

        $this->assertTrue(is_float($oneDollarInEuro));
        $this->assertTrue(is_float($onePoundInEuro));

        // dollar in pound
        $oneDollarInPound = $USD->convert(1, 'GBP');

        $this->assertTrue(is_float($oneDollarInPound));

        // alles wieder zurück, wenn das klappt, passen die rechnung
        $PoundToDollar = $GBP->convert($oneDollarInPound, 'USD');
        $DollarToEuro  = $USD->convert($usd, 'EUR');

        $this->assertEquals(1, $PoundToDollar);
        $this->assertEquals(1, $DollarToEuro);
    }

    public function testConvertFormat()
    {
        $EUR = new QUI\ERP\Currency\Currency('EUR');
        $usd = $EUR->convertFormat(1, 'USD');

        $this->assertTrue(is_string($usd));
    }
}
