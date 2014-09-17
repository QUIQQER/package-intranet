<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_hasSocialAccess()
 */

/**
 * Return true | false if the user registered with social media
 *
 * @return Bool
 */

function package_quiqqer_intranet_ajax_user_hasSocialAccess($email, $socialType)
{
    $Users = \QUI::getUsers();

    // falls es den benutzer schon gibt
    if ( $Users->existsUsername( $email ) )
    {
        $User = $Users->getUserByName( $email );

    } else if ( $Users->existEmail( $email ) )
    {
        $User = $Users->getUserByMail( $email );

    } else
    {
        return false;
    }

    $Registration = new \QUI\Intranet\Registration();
    $Social       = $Registration->getSocial( $socialType );

    return $Social->hasAccess( $User );
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_hasSocialAccess',
    array( 'email', 'socialType' )
);
