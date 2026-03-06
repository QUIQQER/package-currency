<?php

namespace QUITests\ERP\Currency;

use PHPUnit\Framework\TestCase;
use QUI\ERP\Currency\Calc;
use QUI\ERP\Currency\Currency;
use QUI\ERP\Currency\Handler;

class CalcUnitTest extends TestCase
{
    private Currency $EUR;
    private Currency $USD;
    private Currency $GBP;
    private ?Currency $originalDefault = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->EUR = $this->createCurrency('EUR', 1.0);
        $this->USD = $this->createCurrency('USD', 1.2);
        $this->GBP = $this->createCurrency('GBP', 0.8);

        $this->originalDefault = $this->getDefaultCurrencyFromHandler();
        $this->setDefaultCurrencyOnHandler($this->EUR);
    }

    protected function tearDown(): void
    {
        $this->setDefaultCurrencyOnHandler($this->originalDefault);
        parent::tearDown();
    }

    public function testConvertWithCurrencyObjects(): void
    {
        $result = Calc::convert(10, $this->EUR, $this->USD);

        $this->assertIsFloat($result);
        $this->assertEqualsWithDelta(12.0, $result, 0.0001);
    }

    public function testConvertWithSignReturnsString(): void
    {
        $result = Calc::convertWithSign(10, $this->EUR, $this->USD);

        $this->assertIsString($result);
        $this->assertNotSame('', $result);
    }

    public function testGetExchangeRateBetween(): void
    {
        $result = Calc::getExchangeRateBetween($this->USD, $this->GBP);

        $this->assertIsFloat($result);
        $this->assertEqualsWithDelta(1.5, $result, 0.0001);
    }

    public function testGetExchangeRateBetweenReturnsFalseForZeroRate(): void
    {
        $zeroCurrency = $this->createCurrency('ZER', 0.0);

        $result = Calc::getExchangeRateBetween($this->USD, $zeroCurrency);
        $this->assertFalse($result);
    }

    private function createCurrency(string $code, float $rate): Currency
    {
        return new Currency([
            'currency' => $code,
            'rate' => $rate,
            'autoupdate' => 1,
            'precision' => 2,
            'customData' => []
        ]);
    }

    private function setDefaultCurrencyOnHandler(?Currency $Currency): void
    {
        $reflection = new \ReflectionClass(Handler::class);
        $property = $reflection->getProperty('Default');
        $property->setAccessible(true);
        $property->setValue(null, $Currency);
    }

    private function getDefaultCurrencyFromHandler(): ?Currency
    {
        $reflection = new \ReflectionClass(Handler::class);
        $property = $reflection->getProperty('Default');
        $property->setAccessible(true);

        return $property->getValue();
    }
}
