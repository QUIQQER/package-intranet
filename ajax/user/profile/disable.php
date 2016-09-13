<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_profile_disable
 */

/**
 * Start the User deletion process
 *
 * @return bool
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_intranet_ajax_user_profile_disable',
    function () {
        $User = QUI::getUserBySession();

        if (!$User->getId()) {
            return false;
        }

        $Reg = new QUI\Intranet\Registration();
        $Reg->sendDisableMail($User);

        return true;
    }
);
