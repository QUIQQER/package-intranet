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
 * @param String $project
 * @return string
 * @throws \QUI\Exception
 */

function package_quiqqer_intranet_ajax_user_register($email, $password, $data, $project)
{
    $Users   = \QUI::getUsers();
    $Project = false;

    if ( $Users->emailExists( $email ) || $Users->usernameExists( $email ) )
    {
        throw new \QUI\Exception(
            \QUI::getLocale()->get( 'quiqqer/intranet', 'exception.email.not.allowed' )
        );
    }


    try
    {
        $Project = QUI::getProjectManager()->decode( $project );

    } catch ( \QUI\Exception $Exception )
    {

    }

    $data = json_decode( $data, true );
    $Reg  = new \QUI\Intranet\Registration(array(
        'Project' => $Project
    ));

    $User = $Reg->register(array(
        'nickname' => $email,
        'email'    => $email,
        'password' => $password
    ));

    return \QUI::getLocale()->get( 'quiqqer/intranet', 'message.registration.finish' );
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_register',
    array( 'email', 'password', 'data', 'project' )
);
