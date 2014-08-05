<?php

/**
 * registration template
 *
 * @author www.pcsg.de (Henning Leutz)
 */

/**
 * activation
 */
if ( isset( $_REQUEST['code'] ) && isset( $_REQUEST['nickname'] ) )
{
    try
    {
        $Registration = new \QUI\Intranet\Registration(array(
            'Project' => $Project
        ));

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
