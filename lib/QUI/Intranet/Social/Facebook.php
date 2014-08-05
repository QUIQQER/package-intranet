<?php

/**
 * This file contains \QUI\Intranet\Social\Facebook
 */

namespace QUI\Intranet\Social;

/**
 * Registration with google plus
 *
 * @author www.pcsg.de (Henning Leutz)
 */

class Facebook implements \QUI\Intranet\Interfaces\Social
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
        $this->checkToken( $token );


        $Plugin = \QUI::getPluginManager()->get( 'quiqqer/intranet' );

        $Facebook = new Facebook(array(
            'appId'  => $Plugin->getSettings( 'facebook', 'appId' ),
            'secret' => $Plugin->getSettings( 'facebook', 'secret' ),
            'sharedSession' => true,
            'cookie'        => true
        ));

        $Token = json_decode( $token );

        $Facebook->setAccessToken( $Token->accessToken );

        // Get User ID
        $user = $Facebook->getUser();

        if ( !$user )
        {
            throw new \QUI\Exception(
                \QUI::getLocale(
                    'quiqqer/intranet',
                    'exception.social.facebook.user.not.found'
                )
            );
        }


        try
        {
            $user_profile = $Facebook->api( '/me' );

        } catch ( FacebookApiException $Exception )
        {
            throw new \QUI\Exception(
                \QUI::getLocale(
                    'quiqqer/intranet',
                    'exception.social.google.user.not.found'
                )
            );
        }

        $User = $Users->getUserByMail( $user_profile['email'] );

        if ( !isset( $extra['facebookid'] ) ||
             $extra['facebookid'] != $User->getAttribute( 'facebookid' ) )
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
     * Check if the user is authenticated
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
     * @return Bool
     */
    public function checkToken($token)
    {
        $Token = json_decode( $token );

        if ( !$Token->accessToken )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.social.facebook.wrong.token'
                )
            );
        }

        return true;
    }

}