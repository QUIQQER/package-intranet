<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_save()
 */

/**
 * Save user data
 */

function package_quiqqer_intranet_ajax_user_save($data)
{
    $data = json_decode( $data, true );
    $User = \QUI::getUserBySession();

    $User->setAttributes( $data );
    $User->save();
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_save',
    array( 'data' )
);
