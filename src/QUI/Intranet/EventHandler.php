<?php

/**
 * This file contains \QUI\Intranet\EventHandler
 */

namespace QUI\Intranet;

use QUI;

/**
 * Intranet
 *
 * @author www.pcsg.de
 */

class EventHandler
{
    /**
     * on event : onTemplateGetHeader
     *
     * @param \QUI\Template $TemplateManager
     */
    public static function onTemplateGetHeader(QUI\Template $TemplateManager)
    {
        $TemplateManager->addOnloadJavaScriptModule('package/quiqqer/intranet/bin/page/Load');
    }
}
