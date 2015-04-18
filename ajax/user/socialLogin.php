<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_socialRegister()
 */

/**
 * Register an user with a social network
 *
 * @param String $socialType - type of the social network
 * @param String $token
 * @param String $project    - decoded project data | JSON Array
 *
 * @return array - user attributes
 */

function package_quiqqer_intranet_ajax_user_socialLogin(
    $socialType,
    $token,
    $project
) {
    $Project = false;

    try {
        $Project = \QUI::getProjectManager()->decode($project);

    } catch (\QUI\Exception $Exception) {

    }

    $Reg = new \QUI\Intranet\Registration(array(
        'Project' => $Project
    ));

    $Social = $Reg->getSocial($socialType);
    $User = $Social->login($token);

    if ($User->getId()) {
        $Reg->setLoginData($User);
    }

    return $User->getAttributes();
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_socialLogin',
    array('socialType', 'token', 'project')
);
