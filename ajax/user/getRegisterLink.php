<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_register()
 */

/**
 * Return the registration link
 *
 * @param String $project - project data, JSON Array
 * @return string
 */
function package_quiqqer_intranet_ajax_user_getRegisterLink($project)
{
    $Project = \QUI::getProjectManager()->decode( $project );
    $sites   = $Project->getSites(array(
        'where' => array(
            'type' => 'quiqqer/intranet:intranet/registration'
        ),
        'limit' => 1
    ));

    return isset( $sites[ 0 ] ) ? $sites[ 0 ]->getUrlRewrited() : '';
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_getRegisterLink',
    array( 'project' )
);
