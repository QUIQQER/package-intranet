<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_delete()
 */

/**
 * Start the User deletion process
 */

function package_quiqqer_intranet_ajax_user_profile_disable()
{
    $User = \QUI::getUserBySession();

    if ( !$User->getId() ) {
        return;
    }

    $Reg = new \QUI\Intranet\Registration();
    $Reg->sendDisableMail( $User );

    return true;
}

\QUI::$Ajax->register( 'package_quiqqer_intranet_ajax_user_profile_disable' );
