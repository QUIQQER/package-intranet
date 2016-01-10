<?php

/**
 * This file contains \QUI\Intranet
 */

namespace QUI;

use QUI;

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
    public static function onTemplateGetHeader(QUI\Template $TemplateManager)
    {
        $TemplateManager->addOnloadJavaScriptModule('package/quiqqer/intranet/bin/page/Load');
    }
}
