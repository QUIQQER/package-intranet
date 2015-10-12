<?php

/**
 * Delete an addresse
 *
 * @param Integer $aid - Address-ID
 */

function package_quiqqer_intranet_ajax_address_delete($aid)
{
    $User = \QUI::getUserBySession();
    $Address = $User->getAddress($aid);

    $Address->delete();
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_address_delete',
    array('aid'),
    'Permission::checkUser'
);
