<?php

/**
 * This file contains \QUI\Intranet\Social\Facebook
 */

namespace QUI\Intranet\Social;

use QUI;
use QUI\Users\User;

/**
 * Registration with google plus
 *
 * @author www.pcsg.de (Henning Leutz)
 */

class Facebook implements QUI\Intranet\Interfaces\Social
{
    /**
     * Checks if the token is correct
     *
     * @param string $token
     * @return Bool
     * @throws \QUI\Exception
     */
    public function checkToken($token)
    {
        $Token = json_decode( $token );

        if ( !$Token->accessToken )
        {
            throw new QUI\Exception(
                QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.social.facebook.wrong.token'
                )
            );
        }

        return true;
    }

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

        $data = $this->getUserDataByToken( $token );

        $Users = QUI::getUsers();
        $User  = $Users->getUserByMail( $data['email'] );

        if ( !isset( $data['id'] ) || $data['id'] != $User->getAttribute( 'quiqqer.intranet.facebookid' ) )
        {
            throw new QUI\Exception(
                QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.social.facebook.user.not.found'
                ),
                404
            );
        }

        return $User;
    }

    /**
     * (non-PHPdoc)
     * @see \QUI\Intranet\Interfaces\Social::getUserDataByToken()
     *
     * @param string $token
     * @return array
     * @throws QUI\Exception
     */
    public function getUserDataByToken($token)
    {
        $this->checkToken( $token );

        $Plugin         = QUI::getPluginManager()->get( 'quiqqer/intranet' );
        $facebookAppId  = $Plugin->getSettings( 'social', 'facebookAppId' );
        $facebookSecret = $Plugin->getSettings( 'social', 'facebookSecret' );

        if ( empty( $facebookSecret ) || empty( $facebookAppId ) )
        {
            QUI\System\Log::write(
                QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.intranet.social.facebook.missing.config'
                ),
                QUI\System\Log::LEVEL_ERROR
            );

            throw new QUI\Exception(
                QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.intranet.social.facebook.missing.config'
                )
            );
        }

        $Facebook = new \Facebook(array(
            'appId'  => $facebookAppId,
            'secret' => $facebookSecret,
            'sharedSession' => true,
            'cookie'        => true
        ));

        $Token = json_decode( $token );

        $Facebook->setAccessToken( $Token->accessToken );

        // Get User ID
        $user = $Facebook->getUser();

        if ( !$user )
        {
            throw new QUI\Exception(
                QUI::getLocale(
                    'quiqqer/intranet',
                    'exception.social.facebook.user.not.found'
                )
            );
        }


        try
        {
            return $Facebook->api( '/me' );

        } catch ( \FacebookApiException $Exception )
        {
            throw new QUI\Exception(
                QUI::getLocale(
                    'quiqqer/intranet',
                    'exception.social.facebook.user.not.found'
                )
            );
        }
    }

    /**
     * (non-PHPdoc)
     * @see \QUI\Intranet\Interfaces\Social::hasAccess()
     *
     * @param User $User
     * @return string
     */
    public function hasAccess(User $User)
    {
        return $User->getAttribute( 'quiqqer.intranet.facebookid' );
    }

    /**
     * Check if the user is authenticated
     *
     * @param string $token
     * @return false|\QUI\Users\User
     */
    public function isAuth($token)
    {
        try
        {
            $this->getUserDataByToken( $token );

            return true;

        } catch ( QUI\Exception $Exception )
        {
            return false;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \QUI\Intranet\Interfaces\Social::login()
     *
     * @param string $token
     * @return User
     * @throws QUI\Exception
     */
    public function login($token)
    {
        $User = $this->getUserByToken( $token );

        if ( !$User->isActive() ) {
            throw new QUI\Exception( 'User is not activated' );
        }

        // social media, user is directly loged in
        QUI::getSession()->set( 'uid', $User->getId() );
        QUI::getSession()->set( 'auth', 1 );

        return $User;
    }

    /**
     * (non-PHPdoc)
     * @see \QUI\Intranet\Interfaces\Social::onRegistration()
     *
     * @param User $User
     * @param string $token
     */
    public function onRegistration(User $User, $token)
    {
        $data = $this->getUserDataByToken( $token );

        if ( isset( $data['first_name'] ) ) {
            $User->setAttribute( 'firstname', $data['first_name'] );
        }

        if ( isset( $data['first_name'] ) ) {
            $User->setAttribute( 'lastname', $data['last_name'] );
        }

        if ( isset( $data['locale'] ) )
        {
            $locale = explode( '_', $data['locale'] );
            $lang   = $locale[0];
            $langs  = QUI::availableLanguages();

            $userLang = QUI::getLocale()->getCurrent();

            if ( in_array( $lang, $langs ) ) {
                $userLang = $lang;
            }

            $User->setAttribute( 'lang', $userLang );
        }

        $User->setAttribute( 'quiqqer.intranet.facebookid', $data['id'] );
        $User->save();
    }
}
