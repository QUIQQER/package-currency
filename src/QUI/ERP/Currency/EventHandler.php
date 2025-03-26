<?php

/**
 * This file contains \QUI\ERP\Currency\EventHandler
 */

namespace QUI\ERP\Currency;

use QUI;
use QUI\Package\Package;

use function json_encode;

/**
 * Class EventHandler
 * @package QUI\ERP\Currency
 */
class EventHandler
{
    /**
     * @param QUI\Template $TemplateManager
     */
    public static function onTemplateGetHeader(QUI\Template $TemplateManager): void
    {
        $TemplateManager->extendHeader(
        '<script>
                window.DEFAULT_CURRENCY = "' . Handler::getDefaultCurrency()->getCode() . '";
                window.RUNTIME_CURRENCY = ' . Handler::getRuntimeCurrency()->getCode() . ';
            </script>'
        );

        $UserCurrency = Handler::getUserCurrency();

        if ($UserCurrency) {
            $TemplateManager->extendHeader(
                '<script>
                    window.DEFAULT_USER_CURRENCY = ' . json_encode($UserCurrency->toArray()) . ';
                </script>'
            );
        }
    }

    /**
     * Clears the currency list cache if the package config of 'quiqqer/currency' is saved
     *
     * @param Package $Package The package object.
     * @param array $params Additional parameters passed to the method.
     * @return void
     */
    public static function onPackageConfigSave(Package $Package, array $params): void
    {
        if ($Package->getName() !== 'quiqqer/currency') {
            return;
        }

        QUI\Cache\Manager::clear('quiqqer/currency/list');
    }
}
