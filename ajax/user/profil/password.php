<?php

/**
 * Return user data template
 */

function package_quiqqer_intranet_ajax_user_profil_password()
{
    $User   = \QUI::getUserBySession();
    $Engine = \QUI\Template::getEngine();

    $Engine->assign(array(
        'User' => $User
    ));

    return $Engine->fetch(
        OPT_DIR .'quiqqer/intranet/ajax/user/profil/password.html'
    );
}

\QUI::$Ajax->register( 'package_quiqqer_intranet_ajax_user_profil_password' );
