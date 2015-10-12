<?php

/**
 * Edit addresse
 */

function package_quiqqer_intranet_ajax_address_edit($aid, $data)
{
    $User = \QUI::getUserBySession();
    $Address = $User->getAddress($aid);

    $Address->setAttributes(json_decode($data, true));
    $Address->save();
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_address_edit',
    array('aid', 'data'),
    'Permission::checkUser'
);
