<?php

/**
 * Return user data template
 */

function package_quiqqer_intranet_ajax_user_profile_password()
{
    $User = \QUI::getUserBySession();
    $Engine = \QUI::getTemplateManager()->getEngine();

    $Engine->assign(array(
        'User' => $User
    ));

    return $Engine->fetch(
        OPT_DIR.'quiqqer/intranet/ajax/user/profile/password.html'
    );
}

\QUI::$Ajax->register('package_quiqqer_intranet_ajax_user_profile_password');
