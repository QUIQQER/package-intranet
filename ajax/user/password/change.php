<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_password_change
 */

/**
 * User pw change
 *
 * @param string $password1
 * @param string $password2
 * @param string $oldpassword
 * @return array
 *
 * @throws QUI\Exception
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_intranet_ajax_user_password_change',
    function (
        $password1,
        $password2,
        $oldpassword
    ) {
        $User = QUI::getUserBySession();

        if (empty($oldpassword)) {
            throw new QUI\Exception(
                QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.old.pw.empty'
                )
            );
        }

        if (!$User->checkPassword($oldpassword)) {
            throw new QUI\Exception(
                QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.old.pw.not.correct'
                )
            );
        }

        if ($password1 != $password2) {
            throw new \QUI\Exception(
                QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.pws.not.match'
                )
            );
        }

        $User->setPassword($password1);
    },
    array('password1', 'password2', 'oldpassword')
);
