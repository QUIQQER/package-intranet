<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_password_forgotten()
 */

/**
 * Send an password forgotten mail
 *
 * @return Array
 */

function package_quiqqer_intranet_ajax_user_password_forgotten($user, $project, $lang)
{
    $Reg = new \QUI\Intranet\Registration(array(
        'Project' => \QUI::getProject( $project, $lang )
    ));

    $Reg->sendPasswordForgottenMail( $user );
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_password_forgotten',
    array( 'user', 'project', 'lang' )
);
