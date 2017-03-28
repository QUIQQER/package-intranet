<?php

/**
 * Return user data template
 *
 * @return String
 */

QUI::$Ajax->registerFunction(
    'package_quiqqer_intranet_ajax_user_profile_data',
    function () {
        $User   = QUI::getUserBySession();
        $Engine = QUI::getTemplateManager()->getEngine();

        $Engine->assign(array(
            'User' => $User
        ));

        return $Engine->fetch(
            OPT_DIR . 'quiqqer/intranet/ajax/user/profile/data.html'
        );
    }
);
