<?php

/**
 * Return all addresses from an user
 *
 * @return Array
 */
function package_quiqqer_intranet_ajax_address_list()
{
    $User = \QUI::getUserBySession();

    $addresses = $User->getAddressList();
    $result   = array();

    foreach ( $addresses as $Address )
    {
        $entry       = $Address->getAllAttributes();
        $entry['id'] = $Address->getId();

        $result[] = $entry;
    }

    return $result;
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_address_list',
    array('uid'),
    'Permission::checkUser'
);
