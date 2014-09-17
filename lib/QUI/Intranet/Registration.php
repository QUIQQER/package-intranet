<?php

/**
 * This file contains \QUI\Intranet\Registration;
 */

namespace QUI\Intranet;

use \QUI\Utils\Security\Orthos as Orthos;

/**
 * QUIQQER Registration
 *
 * @author www.pcsg.de (Henning Leutz)
 *
 * @example new \QUI\Intranet\Registration();
 *
 * @example new \QUI\Intranet\Registration(array(
 * 		'Project' => $Project
 * ));
 */

class Registration extends \QUI\QDOM
{
    /**
     * constructor
     *
     * @param array $params
     * 	Project => \QUI\Projects\Project
     */
    public function __construct($params=array())
    {
        $this->setAttributes( $params );
    }

    /**
     * Register a user over $data params
     *
     * $data['nickname']
     * $data['email']
     * $data['password']
     * $data['password2']
     * $data['agbs']
     */
    public function register($data)
    {
        if ( !isset( $data['nickname'] ) )
        {
            // Bitte geben Sie einen Benutzernamen an
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.enter.username'
                )
            );
        }

        if ( !isset( $data['email'] ) )
        {
            // Bitte geben Sie eine E-Mail Adresse an
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.enter.mail'
                )
            );
        }

        if ( !Orthos::checkMailSyntax( $data['email'] ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.enter.correct.mail'
                )
            );
        }

        // Passwort Prüfung
        if ( !isset( $data['password'] ) || empty( $data['password'] ) )
        {
            // Bitte geben Sie ein Passwort ein
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.enter.pw'
                )
            );
        }

        $Users = \QUI::getUsers();

        if ( $Users->existsUsername( $data['nickname'] ) )
        {
            // Bitte verwenden Sie einen andere Benutzernamen
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.different.username'
                )
            );
        }

        $Plugin   = \QUI::getPluginManager()->get( 'quiqqer/intranet' );
        $groupids = $Plugin->getSettings('registration', 'standardGroups');

        // user default language
        $langs    = \QUI::availableLanguages();
        $newLang  = \QUI::getLocale()->getCurrent();
        $userLang = 'en';

        if ( in_array( $newLang, $langs ) ) {
            $userLang = $newLang;
        }

        $User = $Users->register(array(
            'username'  => $data['nickname'],
            'password'  => $data['password'],
            'email'     => $data['email'],
            'usergroup' => $groupids,
            'lang'      => $userLang
        ));

        if ( $Plugin->getSettings('registration', 'sendMailOnRegistration') ) {
            $this->sendRegistrationMail( $User );
        }

        return $User;
    }

    /**
     * Register an user with social media network
     *
     * @param String $socialType - Social media name
     * @param Array $socialData - Social media data
     * @throws \QUI\Exception
     */
    public function socialRegister($socialType, $socialData)
    {
        $Users = \QUI::getUsers();

        if ( !isset( $socialData[ 'email' ] ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.social.registration.cannot.excecute'
                )
            );
        }

        $email = $socialData[ 'email' ];

        if ( $Users->existsUsername( $email ) || $Users->existEmail( $email ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.user.exists'
                )
            );
        }

        // social auth check
        $token = array();

        if ( isset( $socialData['token'] ) ) {
            $token = json_encode( $socialData['token'] );
        }

        $Social = $this->getSocial( $socialType );

        if ( !$Social->isAuth( $token ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.social.network.no.token'
                )
            );
        }


        // user creation
        $Plugin   = \QUI::getPluginManager()->get( 'quiqqer/intranet' );
        $groupids = $Plugin->getSettings('registration', 'standardGroups');

        // user default language
        $langs    = \QUI::availableLanguages();
        $newLang  = \QUI::getLocale()->getCurrent();
        $userLang = 'en';

        if ( in_array( $newLang, $langs ) ) {
            $userLang = $newLang;
        }

        $User = $Users->register(array(
            'username'  => $email,
            'password'  => md5( mt_rand(0, 100000) ),
            'email'     => $email,
            'usergroup' => $groupids,
            'lang'      => $userLang
        ));

        // social media, user is directly loged in
        \QUI::getSession()->set( 'uid', $User->getId() );
        \QUI::getSession()->set( 'auth', 1 );

        // social media data
        $Social->onRegistration( $User, $token );

        // user via social media are directly activated
        $this->activate( $User->getId(), $User->getAttribute( 'activation' ) );
    }

    /**
     * Get social type object
     *
     * @param String $socialType
     *
     * @throws \QUI\Exception
     * @return \QUI\Intranet\Social\Google|\QUI\Intranet\Social\Facebook
     */
    public function getSocial($socialType)
    {
        if ( $socialType == 'google' ) {
            return new \QUI\Intranet\Social\Google();
        }

        if ( $socialType == 'facebook' ) {
            return new \QUI\Intranet\Social\Facebook();
        }

        throw new \QUI\Exception(
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'exception.social.network.unknown'
            )
        );
    }

    /**
     * Activate an deactivated user with its code and user-id
     *
     * @param Integer|String $uid - use-id or username
     * @param String $code
     */
    public function activate($uid, $code)
    {
        if ( empty( $code ) )
        {
            // Es wurde kein Aktivierungscode übermittelt
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.no.activation.code'
                )
            );
        }

        if ( empty( $uid ) )
        {
            // Bitte geben Sie einen Benutzernamen an
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.enter.username'
                )
            );
        }


        $Users = \QUI::getUsers();

        try
        {
            // username?
            $User = $Users->getUserByName( $uid );

        } catch ( \QUI\Exception $Exception )
        {
            // user id?
            $User = $Users->get( $uid );
        }

        $User->activate( $code );
        $this->sendActivasionSuccessMail( $User );

        return $User;
    }

    /**
     * helper methods
     */

    /**
     * Return the project for the intranet plugin
     *
     * @return \QUI\Projects\Project
     */
    protected function _getProject()
    {
        if ( $this->getAttribute('Project') ) {
            return $this->getAttribute('Project');
        }

        return \QUI::getProjectManager()->get();
    }

    /**
     * Return registration site -> quiqqer/intranet:intranet/registration
     *
     * @throws \QUI\Exception
     * @return \QUI\Projects\Site\Edit
     */
    protected function _getRegSite()
    {
        $Project = $this->_getProject();

        $list = $Project->getSites(array(
            'where' => array(
                'type' => 'quiqqer/intranet:intranet/registration'
            ),
            'limit' => 1
        ));

        if ( !isset( $list[0] ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.registrationSite.not.found'
                )
            );
        }

        return $list[0];
    }

    /**
     * Return the User by mail, id, username
     *
     * @param String|Integer $user
     * @return \QUI\Users\User
     */
    protected function _getUser($user)
    {
        $Users = \QUI::getUsers();

        if ( $Users->existsUsername( $user ) )  {
            return $Users->getUserByName( $user );
        }

        if ( $Users->existEmail( $user ) ) {
            return $Users->getUserByMail( $user );
        }

        if ( (int)$user == $user ) {
            return $Users->get( (int)$user );
        }

        throw new \QUI\Exception(
            \QUI::getLocale()->get(
                'quiqqer/system',
                'exception.lib.user.wrong.uid'
            ),
            404
        );
    }

    /**
     * Mail Methods
     */

    /**
     * Send a registration mail to the user
     *
     * @param \QUI\Users\User $User
     * @param \QUI\Projects\Site $Site - [optional]
     */
    public function sendRegistrationMail(\QUI\Users\User $User, $Site=false)
    {
        $Project = $this->_getProject();
        $Locale  = \QUI::getLocale();
        $project = $Project->getAttribute('name');

        // if no site, find a registration site
        if ( !$Site ) {
            $Site = $this->_getRegSite();
        }

        // create registration mail
        $reg_url  = $Project->getVHost( true ) . URL_DIR . $Site->getUrlRewrited() .'?';
        $reg_url .= 'code='. $User->getAttribute('activation') .'&';
        $reg_url .= 'nickname='. $User->getName();


        $MAILFromText = '';
        $MailSubject  = '';

        if ( $Locale->exists( 'project/'. $project, 'intranet.registration.MAILFromText' ) )
        {
            $MAILFromText = $Locale->get( 'project/'. $project, 'intranet.registration.MAILFromText' );
        } else
        {
            $MAILFromText = $Locale->get( 'quiqqer/intranet', 'mail.registration.MAILFromText' );
        }

        if ( $Locale->exists( 'project/'. $project, 'intranet.registration.MailSubject' ) )
        {
            $MailSubject = $Locale->get( 'project/'. $project, 'intranet.registration.MailSubject' );
        } else
        {
            $MailSubject = $Locale->get( 'quiqqer/intranet', 'mail.registration.MailSubject' );
        }

        $MailBody = \QUI::getLocale()->get(
            'quiqqer/intranet',
            'mail.registration.Body',
            array( 'registration_url' => $reg_url )
        );

        // send mail
        $Mail = new \QUI\Mail\Mailer();

        $Mail->setProject( $this->_getProject() );
        $Mail->setFromName( $MAILFromText );
        $Mail->setSubject( $MailSubject );
        $Mail->setBody( $MailBody );
        $Mail->addRecipient( $User->getAttribute('email') );

        if ( !$Mail->send() )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.send.registration.mail.fail'
                )
            );
        }
    }

    /**
     * Send an activasion success mail
     *
     * @param \QUI\Users\User $User
     * @param \QUI\Projects\Site $Site - [optional]
     */
    public function sendActivasionSuccessMail(\QUI\Users\User $User, $Site=false)
    {
        $Project = $this->_getProject();
        $Locale  = \QUI::getLocale();
        $Engine  = \QUI::getTemplateManager()->getEngine();

        if ( !isset( $Site ) ) {
            $Site = $this->_getRegSite();
        }

        /**
         * Registrierungs Mail
         */
        $Engine->assign(array(
            'Project' => $Project,
            'Site'    => $Site,
            'User'    => $User
        ));

        $Plugin = \QUI::getPluginManager()->get( 'quiqqer/intranet' );

        // Mail vars
        $MAILFromText = '';
        $MailSubject  = '';

        $Locale  = \QUI::getLocale();
        $project = $Project->getAttribute('name');

        // schauen ob es übersetzungen dafür gibt
        if ( $Locale->exists('project/'. $project, 'intranet.activation.MAILFromText') )
        {
            $MAILFromText = $Locale->get('project/'. $project, 'intranet.activation.MAILFromText');
        } else
        {
            $MAILFromText = $Locale->get('quiqqer/intranet', 'mail.activation.MAILFromText');
        }


        if ( $Locale->exists('project/'. $project, 'intranet.activation.Subject') )
        {
            $MailSubject = $Locale->get('project/'. $project, 'intranet.activation.MailSubject');
        } else
        {
            $MailSubject = $Locale->get('quiqqer/intranet', 'mail.activation.MailSubject');
        }

        $MailBody = \QUI::getLocale()->get(
            'quiqqer/intranet',
            'mail.activation.Body'
        );


        // send mail
        $Mail = new \QUI\Mail\Mailer();

        $Mail->setProject( $this->_getProject() );
        $Mail->setSubject( $MailSubject );
        $Mail->setFromName( $MAILFromText );
        $Mail->addRecipient( $User->getAttribute('email') );
        $Mail->setBody( $MailBody );

        if ( !$Mail->send() )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.send.activation.successful.mail.fail'
                )
            );
        }
    }

    /**
     * Sends a password forgotten Mail
     *
     * @param Integer|String $user
     * @param \QUI\Projects\Project $Project
     */
    public function sendPasswordForgottenMail($user)
    {
        $Project = $this->_getProject();
        $User    = $this->_getUser( $user );
        $Users   = \QUI::getUsers();
        $Engine  = \QUI::getTemplateManager()->getEngine();

        if ( !$Users->isUser($User) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'system',
                    'exception.lib.user.user.not.found'
                ),
                404
            );
        }


        $RegSite = $this->_getRegSite();
        $hash    = \QUI\Utils\Security\Orthos::getPassword();

        $url = $Project->getVHost( true ) . $RegSite->getUrl(array(
            'uid'  => $User->getId(),
            'pass' => 'new',
            'hash' => $hash
        ), true);

        $User->setAttribute( 'quiqqer.intranet.passwordForgotten.hash', $hash );
        $User->save( $Users->getSystemUser() );


        /**
         * create mail
         */
        $MAILFromText = '';
        $MailSubject  = '';
        $project      = $Project->getName();

        // schauen ob es übersetzungen dafür gibt
        if ( \QUI::getLocale()->exists('project/'. $project, 'intranet.forgotten.password.MAILFromText') )
        {
            $MAILFromText = \QUI::getLocale()->get(
                'project/'. $project,
                'intranet.forgotten.password.MAILFromText'
            );

        } else
        {
            $MAILFromText = \QUI::getLocale()->get(
                'quiqqer/intranet',
                'mail.forgotten.password.MAILFromText'
            );
        }


        if ( \QUI::getLocale()->exists('project/'. $project, 'intranet.forgotten.password.Subject') )
        {
            $MailSubject = \QUI::getLocale()->get(
                'project/'. $project,
                'intranet.forgotten.password.MailSubject'
            );

        } else
        {
            $MailSubject = \QUI::getLocale()->get(
                'quiqqer/intranet',
                'mail.forgotten.password.MailSubject'
            );
        }

        $MailBody = \QUI::getLocale()->get(
            'quiqqer/intranet',
            'mail.forgotten.password.Body',
            array( 'password_url' => $url )
        );


        // send mail
        $Mail = new \QUI\Mail\Mailer();

        $Mail->setProject( $this->_getProject() );
        $Mail->setSubject( $MailSubject );
        $Mail->setFromName( $MAILFromText );
        $Mail->addRecipient( $User->getAttribute('email') );
        $Mail->setBody( $MailBody );

        $Mail->Template->setAttributes(array(
            'Project' => $Project,
            'Site'    => $RegSite,
            'User'    => $User,
            'hash'    => $hash
        ));


        if ( !$Mail->send() )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.send.forgotten.password.mail.fail'
                )
            );
        }

        \QUI::getMessagesHandler()->addSuccess(
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'message.send.forgotten.password.successfully'
            )
        );
    }

    /**
     * set a new password and send the password via mail
     *
     * @param String $user - User E-Mail, User-Id, Username
     * @param String $hash - User password hash
     */
    public function sendNewPasswordMail($user, $hash)
    {
        $Project = $this->_getProject();
        $User    = $this->_getUser( $user );
        $RegSite = $this->_getRegSite();

        $Users   = \QUI::getUsers();
        $Engine  = \QUI::getTemplateManager()->getEngine();


        // Hash Abfrage
        $userHash = $User->getAttribute( 'quiqqer.intranet.passwordForgotten.hash' );

        if ( $userHash != $hash )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.wrong.hash'
                )
            );
        }

        // set new password
        $newpass = \QUI\Utils\Security\Orthos::getPassword();

        $User->setPassword( $newpass, $Users->getSystemUser() );
        $User->save( $Users->getSystemUser() );

        // create mail
        $MAILFromText = '';
        $MailSubject  = '';
        $project      = $Project->getName();

        // schauen ob es übersetzungen dafür gibt
        if ( \QUI::getLocale()->exists('project/'. $project, 'intranet.new.password.MAILFromText') )
        {
            $MAILFromText = \QUI::getLocale()->get(
                'project/'. $project,
                'intranet.new.password.MAILFromText'
            );

        } else
        {
            $MAILFromText = \QUI::getLocale()->get(
                'quiqqer/intranet',
                'mail.new.password.MAILFromText'
            );
        }


        if ( \QUI::getLocale()->exists('project/'. $project, 'intranet.new.password.Subject') )
        {
            $MailSubject = \QUI::getLocale()->get(
                'project/'. $project,
                'intranet.new.password.MailSubject'
            );

        } else
        {
            $MailSubject = \QUI::getLocale()->get(
                'quiqqer/intranet',
                'mail.new.password.MailSubject'
            );
        }

        $MailBody = \QUI::getLocale()->get(
            'quiqqer/intranet',
            'mail.new.password.Body',
            array(
                'username' => $User->getName(),
                'uid'      => $User->getId(),
                'password' => $newpass
            )
        );

        \QUI\System\Log::write( 'sendNewPasswordMail' );

        // send mail
        $Mail = new \QUI\Mail\Mailer();

        $Mail->setProject( $this->_getProject() );
        $Mail->setSubject( $MailSubject );
        $Mail->setFromName( $MAILFromText );
        $Mail->addRecipient( $User->getAttribute('email') );
        $Mail->setBody( $MailBody );

        $Mail->Template->setAttributes(array(
            'Project' => $Project,
            'Site'    => $RegSite,
            'User'    => $User,
            'hash'    => $hash
        ));

        if ( !$Mail->send() )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.send.new.password.mail.fail'
                )
            );
        }

        $User->setAttribute(
            'quiqqer.intranet.passwordForgotten.hash',
            \QUI\Utils\Security\Orthos::getPassword()
        );

        $User->save( $Users->getSystemUser() );

        \QUI::getMessagesHandler()->addSuccess(
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'message.send.new.password.successfully'
            )
        );
    }
}
