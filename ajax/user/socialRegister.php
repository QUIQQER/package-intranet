<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_socialRegister()
 */

/**
 * Register an user over social networks
 *
 * @param String $email
 * @param String $password
 * @param String $data
 */

function package_quiqqer_intranet_ajax_user_socialRegister($socialType, $socialData)
{
    $socialData = json_decode( $socialData, true );

    // create user
    $Reg  = new \QUI\Intranet\Registration();
    $User = $Reg->socialRegister( $socialType, $socialData );

    return true;
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_socialRegister',
    array( 'socialType', 'socialData' )
);
