<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_data()
 */

/**
 * Return user data
 *
 * @return Array
 */

function package_quiqqer_intranet_ajax_user_password_change(
    $password1,
    $password2,
    $oldpassword
) {
    $User = \QUI::getUserBySession();

    if (empty($oldpassword)) {
        throw new \QUI\Exception(
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'exception.old.pw.empty'
            )
        );
    }

    if (!$User->checkPassword($oldpassword)) {
        throw new \QUI\Exception(
            \QUI::getLocale(
                'quiqqer/intranet',
                'exception.old.pw.not.correct'
            )
        );
    }

    if ($password1 != $password2) {
        throw new \QUI\Exception(
            \QUI::getLocale(
                'quiqqer/intranet',
                'exception.pws.not.match'
            )
        );
    }

    $User->setPassword($password1);
}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_password_change',
    array('password1', 'password2', 'oldpassword')
);
