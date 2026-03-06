<?php

namespace QUITests\ERP\Currency;

use PHPUnit\Framework\TestCase;
use QUI;

class HandlerTest extends TestCase
{
    public function testGetDefaultCurrency(): void
    {
        $Currency = QUI\ERP\Currency\Handler::getDefaultCurrency();

        $this->assertNotEmpty($Currency->getText());
        $this->assertNotEmpty($Currency->getSign());
        $this->assertNotEmpty($Currency->getCode());

        // default config check
        $Config = QUI::getPackage('quiqqer/currency')->getConfig();
        $defaultFromSettings = $Config->getValue('currency', 'defaultCurrency');

        $this->assertSame($defaultFromSettings, $Currency->getCode());
    }

    public function testGetData(): void
    {
        $data = QUI\ERP\Currency\Handler::getData();

        $this->assertNotEmpty($data);
    }

    public function testGetCurrency(): void
    {
        $EUR = QUI\ERP\Currency\Handler::getCurrency('EUR');
        $USD = QUI\ERP\Currency\Handler::getCurrency('USD');

        $this->assertSame('EUR', $EUR->getCode());
        $this->assertSame('USD', $USD->getCode());

        $this->assertSame('€', $EUR->getSign());
        $this->assertSame('$', $USD->getSign());
    }

    public function testGetAllowedCurrencies(): void
    {
        $Config = QUI::getPackage('quiqqer/currency')->getConfig();

        $allowed = $Config->getValue('currency', 'allowedCurrencies');
        $allowed = explode(',', trim($allowed));
        $default = QUI\ERP\Currency\Handler::getDefaultCurrency()->getCode();

        $list = QUI\ERP\Currency\Handler::getAllowedCurrencies();
        $this->assertNotEmpty($list);

        foreach ($list as $Currency) {
            $this->assertTrue(
                in_array($Currency->getCode(), $allowed, true) || $Currency->getCode() === $default
            );
        }
    }
}
