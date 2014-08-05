<?php

/**
 * This file contains package_quiqqer_intranet_ajax_user_socialRegister()
 */

/**
 * Register an user over social networks
 *
 * @param String $email
 * @param String $password
 * @param String $data
 */

function package_quiqqer_intranet_ajax_user_socialRegister($socialType, $socialData)
{
    $socialData = json_decode( $socialData, true );
    $token      = array();

    if ( isset( $socialData['token'] ) ) {
        $token = json_encode( $socialData['token'] );
    }

    switch ( $socialType )
    {
        case 'google':
            $Social = new \QUI\Intranet\Social\Google();
        break;

        case 'facebook':
            $Social = new \QUI\Intranet\Social\Facebook();
        break;

        default:
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.social.network.unknown'
                )
            );
    }

    if ( !$Social->isAuth( $token ) )
    {
        throw new \QUI\Exception(
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'exception.social.network.unknown'
            )
        );
    }



    \QUI\System\Log::writeRecursive( $socialData );

    // create user
    $Reg  = new \QUI\Intranet\Registration();
    $User = $Reg->socialRegister( $social, $params );

}

\QUI::$Ajax->register(
    'package_quiqqer_intranet_ajax_user_socialRegister',
    array( 'socialType', 'socialData' )
);
