<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_register()
 */

/**
 * Register an user
 *
 * @param String $email
 * @param String $password
 * @param String $data
 */

function package_quiqqer_intranet_ajax_user_getRegisterLink($project, $lang)
{
    $Project = \QUI::getProject( $project, $lang );
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
    array( 'project', 'lang' )
);
