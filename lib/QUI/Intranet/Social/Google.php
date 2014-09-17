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
     * Google Client
     * @var Null|\Google_Client
     */
    protected $_Client = null;

    /**
     * (non-PHPdoc)
     * @see \QUI\Intranet\Interfaces\Social::onRegistration()
     */
    public function onRegistration(\QUI\Users\User $User, $token)
    {
        $Ticket = $this->checkToken( $token );

        $User->setAttribute( 'quiqqer.intranet.googleid', $Ticket->getUserId() );
        $User->save();
    }

    /**
     * (non-PHPdoc)
     * @see \QUI\Intranet\Interfaces\Social::hasAccess()
     */
    public function hasAccess(\QUI\Users\User $User)
    {
        return $User->getAttribute( 'quiqqer.intranet.googleid' );
    }

    /**
     * (non-PHPdoc)
     * @see \QUI\Intranet\Interfaces\Social::login()
     */
    public function login($token)
    {
        $User = $this->getUserByToken( $token );

        // social media, user is directly loged in
        \QUI::getSession()->set( 'uid', $User->getId() );
        \QUI::getSession()->set( 'auth', 1 );

        return $User;
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
        $Users  = \QUI::getUsers();
        $Ticket = $this->checkToken( $token );

        $attributes = $Ticket->getAttributes();

        // get user from system
        $User = $Users->getUserByMail( $attributes['payload']['email'] );

        if ( $Ticket->getUserId() != $User->getAttribute( 'quiqqer.intranet.googleid' ) )
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
     * (non-PHPdoc)
     * @see \QUI\Intranet\Interfaces\Social::getUserDataByToken()
     */
    public function getUserDataByToken($token)
    {
        $Ticket = $this->checkToken( $token );

        return $Ticket->getAttributes();
    }

    /**
     * Return the gplus Persn
     *
     * @param String $token
     * @return \Google_Service_Plus_Person
     */
    public function getPersonByToken($token)
    {
        $Ticket = $this->checkToken( $token );
        $Plus  = new \Google_Service_Plus( $this->_getClient() );

        return $Plus->people->get( $Ticket->getUserId() );
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
            $this->checkToken( $token );
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
     * @param String $token
     * @return \Google_Auth_LoginTicket
     */
    public function checkToken($token)
    {
        $Client = $this->_getClient();
        $Client->setAccessToken( $token );

        $Ticket = $Client->verifyIdToken();

        if ( !$Ticket )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.social.google.wrong.token'
                )
            );
        }

        return $Ticket;
    }

    /**
     * Return the Google Client
     *
     * @param String $token
     * @return \Google_Client
     */
    protected function _getClient()
    {
        if ( $this->_Client ) {
            return $this->_Client;
        }

        $Plugin = \QUI::getPluginManager()->get( 'quiqqer/intranet' );

        $ApplicationName = $Plugin->getSettings( 'social', 'googleApplicationName' );
        $ClientId        = $Plugin->getSettings( 'social', 'googleClientId' );
        $ClientSecret    = $Plugin->getSettings( 'social', 'googleClientSecret' );


        if ( empty( $ClientId ) || empty( $ClientSecret ) )
        {
            \QUI\System\Log::write(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.intranet.social.google.missing.config'
                ),
                \QUI\System\Log::LEVEL_ERROR
            );

            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.intranet.social.google.missing.config'
                )
            );
        }


        $this->_Client = new \Google_Client();

        $this->_Client->setApplicationName( $ApplicationName );
        $this->_Client->setClientId( $ClientId );
        $this->_Client->setClientSecret( $ClientSecret );

        return $this->_Client;
    }
}