<?php

/**
 * Exists the user in the system?
 *
 * @param String $username - username / e-mail
 */

function package_quiqqer_intranet_ajax_user_existsMail($email)
{
    return \QUI::getUsers()->emailExists( $email );
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_existsMail',
    array( 'email' )
);
