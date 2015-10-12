<?php

/**
 * This file contains \QUI\Intranet
 */

namespace QUI;

/**
 * Intranet
 *
 * @author www.pcsg.de
 */

class Intranet
{
    /**
     * on event : onTemplateGetHeader
     *
     * @param \QUI\Template $TemplateManager
     */
    static function onTemplateGetHeader(\QUI\Template $TemplateManager)
    {
        $TemplateManager->addOnloadJavaScriptModule('package/quiqqer/intranet/bin/page/Load');
    }
}