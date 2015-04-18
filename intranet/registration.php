<?php

/**
 * HTTPs
 */

$httpsHost = $Project->getVHost(true, true);

if (strpos($httpsHost, 'https:') !== false
    && QUI\Utils\System::isProtocolSecure() === false
) {
    QUI::getRewrite()->showErrorHeader(
        301,
        $httpsHost.URL_DIR.$Site->getUrlRewrited()
    );

    exit;
}

/**
 * registration template
 *
 * @author www.pcsg.de (Henning Leutz)
 */

$Registration = new \QUI\Intranet\Registration(array(
    'Project' => $Project
));

$Engine->assign('INTRANET_TYPE', '');

/**
 * activation
 */
if (isset($_REQUEST['code']) && isset($_REQUEST['uid'])) {
    try {
        $Engine->assign('INTRANET_TYPE', 'ACTIVATION');

        $Registration->activate($_REQUEST['uid'], $_REQUEST['code']);

        $Engine->assign(
            'INTRANET_SUCCESS_MESSAGE',
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'message.registration.finish'
            )
        );

    } catch (\QUI\Exception $Exception) {
        $Engine->assign(
            'INTRANET_ERROR_MESSAGE',
            $Exception->getMessage()
        );
    }
}

/**
 * Send new password
 */

if (isset($_REQUEST['uid'])
    && isset($_REQUEST['pass'])
    && isset($_REQUEST['hash'])
    && $_REQUEST['pass'] == 'new'
) {
    try {
        $Engine->assign('INTRANET_TYPE', 'NEW_PASS');

        $Registration->sendNewPasswordMail($_REQUEST['uid'], $_REQUEST['hash']);

        $Engine->assign(
            'INTRANET_SUCCESS_MESSAGE',
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'message.send.new.password.successfully'
            )
        );

    } catch (\QUI\Exception $Exception) {
        $Engine->assign(
            'INTRANET_ERROR_MESSAGE',
            $Exception->getMessage()
        );
    }
}

/**
 * Activate new E-Mail
 */

if (isset($_REQUEST['uid'])
    && isset($_REQUEST['hash'])
    && isset($_REQUEST['type'])
    && $_REQUEST['type'] == 'newMail'
) {
    try {
        $Engine->assign('INTRANET_TYPE', 'ACTIVATE_NEW_EMAIL');

        $Registration->setNewEmail($_REQUEST['uid'], $_REQUEST['hash']);

        $Engine->assign(
            'INTRANET_SUCCESS_MESSAGE',
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'message.new.email.successfully'
            )
        );

    } catch (\QUI\Exception $Exception) {
        $Engine->assign(
            'INTRANET_ERROR_MESSAGE',
            $Exception->getMessage()
        );
    }
}


/**
 * Disable account
 */

if (isset($_REQUEST['uid'])
    && isset($_REQUEST['hash'])
    && isset($_REQUEST['type'])
    && $_REQUEST['type'] == 'disable'
) {
    try {
        $Engine->assign('INTRANET_TYPE', 'DISABLE_ACCOUNT');

        $Registration->disable($_REQUEST['uid'], $_REQUEST['hash']);

        $Engine->assign(
            'INTRANET_DISABLE_MESSAGE',
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'message.disable.successfully'
            )
        );

    } catch (\QUI\Exception $Exception) {
        $Engine->assign(
            'INTRANET_ERROR_MESSAGE',
            $Exception->getMessage()
        );
    }
}

