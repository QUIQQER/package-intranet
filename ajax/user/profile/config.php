<?php

/**
 * Return the intranet profile config
 *
 * @return Array
 */

function package_quiqqer_intranet_ajax_user_profile_config()
{
    $Package = \QUI::getPackageManager()
                   ->getInstalledPackage('quiqqer/intranet');
    $Config = $Package->getConfig();

    $config = array(
        'userProfile' => $Config->getSection('userProfile')
    );

    return $config;
}

\QUI::$Ajax->register('package_quiqqer_intranet_ajax_user_profile_config');
