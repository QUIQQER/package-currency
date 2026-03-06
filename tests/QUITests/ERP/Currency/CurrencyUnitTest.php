<?php

namespace QUITests\ERP\Currency;

use PHPUnit\Framework\TestCase;
use QUI\ERP\Currency\Currency;
use QUI\ERP\Currency\Handler;

class CurrencyUnitTest extends TestCase
{
    private Currency $EUR;
    private Currency $USD;
    private Currency $GBP;
    private ?Currency $originalDefault = null;
    /** @var array<string, array<string, mixed>> */
    private array $originalCurrencies = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->EUR = $this->createCurrency('EUR', 1.0);
        $this->USD = $this->createCurrency('USD', 1.2);
        $this->GBP = $this->createCurrency('GBP', 0.8);

        $this->originalCurrencies = $this->getCurrenciesFromHandler();
        $this->setCurrenciesOnHandler([
            'EUR' => $this->createCurrencyData('EUR', 1.0),
            'USD' => $this->createCurrencyData('USD', 1.2),
            'GBP' => $this->createCurrencyData('GBP', 0.8)
        ]);

        $this->originalDefault = $this->getDefaultCurrencyFromHandler();
        $this->setDefaultCurrencyOnHandler($this->EUR);
    }

    protected function tearDown(): void
    {
        $this->setDefaultCurrencyOnHandler($this->originalDefault);
        $this->setCurrenciesOnHandler($this->originalCurrencies);
        parent::tearDown();
    }

    public function testConvertWithCurrencyObjects(): void
    {
        $usd = $this->EUR->convert(10, $this->USD);
        $eur = $this->USD->convert(12, $this->EUR);
        $gbp = $this->USD->convert(12, $this->GBP);

        $this->assertEqualsWithDelta(12.0, (float)$usd, 0.0001);
        $this->assertEqualsWithDelta(10.0, (float)$eur, 0.0001);
        $this->assertEqualsWithDelta(8.0, (float)$gbp, 0.0001);
    }

    public function testGetExchangeRateToOtherCurrency(): void
    {
        $rate = $this->USD->getExchangeRate($this->EUR);

        $this->assertIsFloat($rate);
        $this->assertEqualsWithDelta(1.2, $rate, 0.0001);
    }

    public function testCustomDataRoundtrip(): void
    {
        $this->EUR->setCustomDataEntry('foo', 'bar');
        $this->assertSame('bar', $this->EUR->getCustomDataEntry('foo'));
    }

    private function createCurrency(string $code, float $rate): Currency
    {
        return new Currency($this->createCurrencyData($code, $rate));
    }

    /**
     * @return array<string, mixed>
     */
    private function createCurrencyData(string $code, float $rate): array
    {
        return [
            'currency' => $code,
            'rate' => $rate,
            'autoupdate' => 1,
            'precision' => 2,
            'customData' => [],
            'type' => 'default'
        ];
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

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getCurrenciesFromHandler(): array
    {
        $reflection = new \ReflectionClass(Handler::class);
        $property = $reflection->getProperty('currencies');
        $property->setAccessible(true);
        $value = $property->getValue();

        return is_array($value) ? $value : [];
    }

    /**
     * @param array<string, array<string, mixed>> $currencies
     */
    private function setCurrenciesOnHandler(array $currencies): void
    {
        $reflection = new \ReflectionClass(Handler::class);
        $property = $reflection->getProperty('currencies');
        $property->setAccessible(true);
        $property->setValue(null, $currencies);
    }
}
