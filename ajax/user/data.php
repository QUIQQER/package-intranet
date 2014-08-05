<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_data()
 */

/**
 * Return user data
 *
 * @return Array
 */

function package_quiqqer_intranet_ajax_user_data()
{
    return \QUI::getUserBySession()->getAttributes();
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_data'
);
