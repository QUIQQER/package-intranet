<?php

/**
 * Create an address
 *
 * @param String $data - JSON data
 */

function package_quiqqer_intranet_ajax_address_create($data)
{
    \QUI::getUserBySession()->addAddress( json_decode( $data, true ) );
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_address_create',
    array( 'data' ),
    'Permission::checkUser'
);
