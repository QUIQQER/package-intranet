<?php

/**
 * Return the profile extend categories
 *
 * @return Array
 */

function package_quiqqer_intranet_ajax_user_profile_getCategories()
{
    return \QUI\Intranet\Utils::getProfileExtendCategories();
}

\QUI::$Ajax->register('package_quiqqer_intranet_ajax_user_profile_getCategories');
