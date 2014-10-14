<?php

/**
 * registration template
 *
 * @author www.pcsg.de (Henning Leutz)
 */

$Registration = new \QUI\Intranet\Registration(array(
    'Project' => $Project
));

/**
 * activation
 */
if ( isset( $_REQUEST['code'] ) && isset( $_REQUEST['nickname'] ) )
{
    try
    {
        $Registration->activate( $_REQUEST['nickname'], $_REQUEST['code'] );

        $Engine->assign(
            'INTRANET_SUCCESS_MESSAGE',
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'message.registration.finish'
            )
        );

    } catch ( \QUI\Exception $Exception )
    {
        $Engine->assign(
            'INTRANET_ERROR_MESSAGE',
            $Exception->getMessage()
        );
    }
}

/**
 * Send new password
 */

if ( isset( $_REQUEST['uid'] ) &&
     isset( $_REQUEST['pass'] ) &&
     isset( $_REQUEST['hash'] ) &&
     $_REQUEST['pass'] == 'new' )
{
    try
    {
        $Registration->sendNewPasswordMail( $_REQUEST['uid'], $_REQUEST['hash'] );

        $Engine->assign(
            'INTRANET_SUCCESS_MESSAGE',
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'message.send.new.password.successfully'
            )
        );

    } catch ( \QUI\Exception $Exception )
    {
        $Engine->assign(
            'INTRANET_ERROR_MESSAGE',
            $Exception->getMessage()
        );
    }
}

/**
 * Disable account
 */

if ( isset( $_REQUEST['uid'] ) &&
     isset( $_REQUEST['hash'] ) &&
     isset( $_REQUEST['type'] ) &&
     $_REQUEST['type'] == 'disable' )
{
    try
    {
        $Registration->disable( $_REQUEST['uid'], $_REQUEST['hash'] );

        $Engine->assign(
            'INTRANET_DISABLE_MESSAGE',
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'message.disable.successfully'
            )
        );

    } catch ( \QUI\Exception $Exception )
    {
        $Engine->assign(
            'INTRANET_ERROR_MESSAGE',
            $Exception->getMessage()
        );
    }


    exit;
}



