<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_socialRegister()
 */

/**
 * Register an user over social networks
 *
 * @param String $socialType
 * @param String $socialData
 * @param String $project - decoded project data | JSON Array
 * @return bool
 * @throws QUI\Exception
 */
function package_quiqqer_intranet_ajax_user_socialRegister($socialType, $socialData, $project)
{
    $socialData = json_decode( $socialData, true );
    $Project    = false;

    try
    {
        $Project = \QUI::getProjectManager()->decode( $project );

    } catch ( \QUI\Exception $Exception )
    {

    }

    // create user
    $Reg = new \QUI\Intranet\Registration(array(
        'Project' => $Project
    ));

    $User = $Reg->socialRegister( $socialType, $socialData );

    if ( !$User->isActive() )
    {
        throw new QUI\Exception(
            QUI::getLocale()->get(
                'quiqqer/intranet',
                'exception.social.registration.cannot.excecute'
            )
        );
    }

    return true;
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_socialRegister',
    array( 'socialType', 'socialData', 'project' )
);
