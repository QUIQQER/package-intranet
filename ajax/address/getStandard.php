<?php

/**
 * Return the standard address of the user
 * If no standard address exist, an address would be created
 *
 * @return Array
 */
function package_quiqqer_intranet_ajax_address_getStandard()
{
    $User = \QUI::getUserBySession();
    $Address = false;

    try {
        $Address = $User->getStandardAddress();

    } catch (\QUI\Exception $Exception) {

    }

    if (!$Address) {
        $Address = $User->addAddress();
    }

    return array_merge(array('id' => $Address->getId()),
        $Address->getAttributes());
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_address_getStandard',
    false,
    'Permission::checkUser'
);
