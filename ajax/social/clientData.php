<?php

/**
 * This file contains package_quiqqer_intranet_ajax_social_clientData()
 */

/**
 * Return social client data
 *
 * @return Array
 */

function package_quiqqer_intranet_ajax_social_clientData()
{
    $Plugin = \QUI::getPluginManager()->get('quiqqer/intranet');

    $facebookAppId = $Plugin->getSettings('social', 'facebookAppId');
    $googleClientId = $Plugin->getSettings('social', 'googleClientId');

    return array(
        'facebookAppId'  => $facebookAppId,
        'googleClientId' => $googleClientId
    );
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_social_clientData'
);
