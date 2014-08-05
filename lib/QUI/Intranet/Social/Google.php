<?php

/**
 * This file contains \QUI\Intranet\Social\Google
 */

namespace QUI\Intranet\Social;

/**
 * Registration with google plus
 *
 * @author www.pcsg.de (Henning Leutz)
 */

class Google implements \QUI\Intranet\Interfaces\Social
{
    /**
     * Return the user by a google token
     *
     * @param String $token
     * @throws \QUI\Exception
     * @return \QUI\Users\User
     */
    public function getUserByToken($token)
    {
        $Users     = \QUI::getUsers();
        $TokenInfo = $this->_getTokenInfo( $token );

        $this->checkToken( $token );

        // get user from system
        $User = $Users->getUserByMail( $TokenInfo->email );

        if ( $TokenInfo->user_id != $User->getAttribute( 'googleid' ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale(
                    'quiqqer/intranet',
                    'exception.social.google.user.not.found'
                )
            );
        }

        return $User;
    }

    /**
     * Check if the user is
     *
     * @param String $token
     * @return false|\QUI\Users\User
     */
    public function isAuth($token)
    {
        try
        {
            $this->getUserByToken( $token );

            return true;

        } catch ( \QUI\Exception $Exception )
        {
            return false;
        }
    }

    /**
     * Checks if the token is correct
     *
     * @throws \QUI\Exception
     */
    public function checkToken($token)
    {
        $TokenInfo = $this->_getTokenInfo( $token );

        $Plugin   = \QUI::getPluginManager()->get( 'quiqqer/intranet' );
        $clientId = $Plugin->getSettings( 'social', 'googleClientId' );

        if ( isset( $TokenInfo->error ) ||
             !isset( $TokenInfo->audience ) ||
             !isset( $TokenInfo->user_id ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.social.google.wrong.token'
                )
            );
        }

        if ( $TokenInfo->audience != $Config->get( 'social', $clientId ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.social.google.wrong.token'
                )
            );
        }
    }

    /**
     * Return the token infos
     *
     * @param String $token
     * @return mixed
     */
    protected function _getTokenInfo($token)
    {
        $Plugin = \QUI::getPluginManager()->get( 'quiqqer/intranet' );
        $Client = new Google_Client();

        $Client->setApplicationName(
            $Plugin->getSettings( 'social', 'googleApplicationName' )
        );

        $Client->setClientId(
            $Plugin->getSettings( 'social', 'googleClientId' )
        );

        $Client->setClientSecret(
            $Plugin->getSettings( 'social', 'googleClientSecret' )
        );


        $Token = json_decode( $token );

        $Req = new Google_HttpRequest(
            'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token='.
            $Token->access_token
        );

        $TokenInfo = json_decode(
            $Client->getIo()->authenticatedRequest( $Req )->getResponseBody()
        );

        return $TokenInfo;
    }
}