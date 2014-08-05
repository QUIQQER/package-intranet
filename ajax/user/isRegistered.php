<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_isRegistered function
 */

/**
 * Return true | false if the user exist
 *
 * @return Bool
 */

function package_quiqqer_intranet_ajax_user_isRegistered($email)
{
    if ( !isset( $email ) || empty( $email )) {
        return false;
    }

    if ( \QUI::getUsers()->existsUsername( $email ) ) {
        return true;
    }

    return \QUI::getUsers()->existEmail( $email );
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_isRegistered',
    array( 'email' )
);
