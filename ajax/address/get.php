<?php

/**
 * Return all addresses from an user
 *
 * @return Array
 */
function package_quiqqer_intranet_ajax_address_get($aid)
{
    $User = \QUI::getUserBySession();
    $Address = $User->getAddress($aid);

    return $Address->getAttributes();
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_address_get',
    array('aid'),
    'Permission::checkUser'
);
