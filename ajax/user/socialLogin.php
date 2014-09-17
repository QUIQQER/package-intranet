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

function package_quiqqer_intranet_ajax_user_socialLogin($socialType, $token)
{
    // create user
    $Reg    = new \QUI\Intranet\Registration();
    $Social = $Reg->getSocial( $socialType );

    $User = $Social->login( $token );

    return $User->getAttributes();
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_socialLogin',
    array( 'socialType', 'token' )
);
