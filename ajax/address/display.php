<?php

/**
 * Return all addresses from an user
 *
 * @return Array
 */
function package_quiqqer_intranet_ajax_address_display($aid)
{
    $User    = \QUI::getUserBySession();
    $Address = $User->getAddress( $aid );

    return $Address->getDisplay();
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_address_display',
    array('aid'),
    'Permission::checkUser'
);
