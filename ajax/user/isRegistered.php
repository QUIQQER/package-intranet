<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_isRegistered function
 */

/**
 * Return true | false if the user exist
 *
 * @param String $email - username | email
 *
 * @return Bool
 */

function package_quiqqer_intranet_ajax_user_isRegistered($email)
{
    if (!isset($email) || empty($email)) {
        return false;
    }

    if (\QUI::getUsers()->usernameExists($email)) {
        return true;
    }

    return \QUI::getUsers()->emailExists($email);
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_isRegistered',
    array('email')
);
