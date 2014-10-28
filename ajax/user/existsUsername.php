<?php

/**
 * Exists the user in the system?
 *
 * @param String $username - username / e-mail
 */

function package_quiqqer_intranet_ajax_user_existsUsername($username)
{
    return \QUI::getUsers()->usernameExists( $username );
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_existsUsername',
    array( 'username' )
);
