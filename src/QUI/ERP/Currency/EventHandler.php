<?php

/**
 * This file contains \QUI\ERP\Currency\EventHandler
 */

namespace QUI\ERP\Currency;

use QUI;

/**
 * Class EventHandler
 * @package QUI\ERP\Currency
 */
class EventHandler
{
    /**
     * @param QUI\Template $TemplateManager
     */
    public static function onTemplateGetHeader(QUI\Template $TemplateManager)
    {
        try {
            $Currency = Handler::getDefaultCurrency();

            $TemplateManager->extendHeader(
                '<script>var DEFAULT_CURRENCY = "'.$Currency->getCode().'";</script>'
            );
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception, QUI\System\Log::LEVEL_WARNING);
        }

        $Currency = Handler::getUserCurrency();

        if ($Currency) {
            $TemplateManager->extendHeader(
                '<script>
                    var DEFAULT_USER_CURRENCY = '.\json_encode($Currency->toArray()).';
                </script>'
            );
        }
    }
}
