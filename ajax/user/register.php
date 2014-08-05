<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_register()
 */

/**
 * Register an user
 *
 * @param String $email
 * @param String $password
 * @param String $data
 */

function package_quiqqer_intranet_ajax_user_register($email, $password, $data)
{
    $Users = \QUI::getUsers();

    if ( $Users->existEmail( $email ) || $Users->existsUsername( $email ) )
    {
        throw new \QUI\Exception(
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'exception.email.not.allowed'
            )
        );
    }

    $data = json_decode( $data, true );
    $Reg  = new \QUI\Intranet\Registration();

    $User = $Reg->register(array(
        'nickname' => $email,
        'email'    => $email,
        'password' => $password
    ));

    return \QUI::getLocale()->get( 'quiqqer/intranet', 'message.registration.finish' );
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_register',
    array( 'email', 'password', 'data' )
);
