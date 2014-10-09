<?php

/**
 * Return all addresses from an user
 *
 * @return Array
 */
function package_quiqqer_intranet_ajax_address_template()
{
    $Engine = \QUI::getTemplateManager()->getEngine();

    $Engine->assign(array(
        'countries' => \QUI\Countries\Manager::getList()
    ));

    return $Engine->fetch( dirname( __FILE__ ) .'/Address.html' );
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_address_template',
    false,
    'Permission::checkUser'
);
