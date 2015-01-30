<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_password_forgotten()
 */

/**
 * Send an password forgotten mail
 *
 * @param string $user - user mail or username
 * @param string $project - project params, JSON Array
 */
function package_quiqqer_intranet_ajax_user_password_forgotten($user, $project)
{
    $Reg = new \QUI\Intranet\Registration(array(
        'Project' => \QUI::getProjectManager()->decode( $project )
    ));

    $Reg->sendPasswordForgottenMail( $user );
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_password_forgotten',
    array( 'user', 'project' )
);
