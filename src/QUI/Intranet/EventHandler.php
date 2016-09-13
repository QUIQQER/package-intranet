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

    /**
     * event on onAdminLoadFooter
     */
    public static function onAdminLoadFooter()
    {
        $User = QUI::getUserBySession();

        if (!$User->getAttribute('quiqqer.intranet.set.new.password')) {
            return;
        }

        $message = QUI::getLocale()->get('quiqqer/intranet', 'message.set.new.password');

        echo "<script>require(['controls/users/password/Window'], function(Password) { 
            new Password({
                message: " . json_encode($message) . "
            }).open(); 
        })</script>";
    }

    /**
     * @param QUI\Interfaces\Users\User $User
     */
    public static function onUserSetPassword(QUI\Interfaces\Users\User $User)
    {
        $User->setAttribute('quiqqer.intranet.set.new.password', 0);
        $User->save(QUI::getUsers()->getSystemUser());
    }
}
