<?php

/**
 * This file contains package_quiqqer_intranet_ajax_logout()
 */

/**
 * Save user data
 */

function package_quiqqer_intranet_ajax_logout()
{
    QUI::getUserBySession()->logout();
}

QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_logout',
    false
);
