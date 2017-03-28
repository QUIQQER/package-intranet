<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_profile_config
 */

/**
 * Return the intranet profile config
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_intranet_ajax_user_profile_config',
    function () {
        $Package = QUI::getPackageManager()->getInstalledPackage('quiqqer/intranet');
        $Config  = $Package->getConfig();

        $config = array(
            'userProfile' => $Config->getSection('userProfile')
        );

        return $config;
    }
);
