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
 *
 * @event onRegistrationUserDisable [ $this ]  -> user starts account deletion
 * @event onRegistrationUserDisabled [ $this ] -> User is deleted / disabled
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
     * Set important login data,
     * if the user loged in via sovial media,
     * session authentication must works, this method helps
     *
     * @param \QUI\Users\User $User
     */
    public function setLoginData(\QUI\Users\User $User)
    {
        $useragent = '';

        if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
            $useragent = $_SERVER['HTTP_USER_AGENT'];
        }

        \QUI::getDataBase()->update(
            \QUI::getUsers()->Table(),
            array(
                'lastvisit'  => time(),
                'user_agent' => $useragent
            ),
            array('id' => $User->getId())
        );
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

        if ( $Plugin->getSettings('registration', 'sendInfoMailOnRegistrationTo') ) {
            $this->_sendInformationRegistrationMailTo( $User );
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

        if ( $Plugin->getSettings('registration', 'sendInfoMailOnRegistrationTo') ) {
            $this->_sendInformationRegistrationMailTo( $User );
        }
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
     * Disable the user
     *
     * @param Integer|String $user
     * @param unknown $code
     */
    public function disable($user, $disableHash)
    {
        $User = $this->_getUser( $user );

        if ( $User->isDeleted() )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.disable.user.already.deleted'
                )
            );
        }


        $Package = \QUI::getPackageManager()->getInstalledPackage( 'quiqqer/intranet' );

        $hashLifetime = $Package->getConfig()->get( 'disable', 'hashLifetime' );
        $hash         = $User->getAttribute( 'quiqqer.intranet.disable.hash' );
        $hashTime     = $User->getAttribute( 'quiqqer.intranet.disable.time' );

        if ( !isset( $Site ) || !$Site ) {
            $Site = $this->_getRegSite();
        }

        if ( $hashTime < time() - $hashLifetime  )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.disable.hash.expired'
                )
            );
        }

        if ( $disableHash != $hash )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.disable.hash.wrong'
                )
            );
        }

        $User->disable( \QUI::getUsers()->getSystemUser() );

        // disable event
        \QUI::getEvents()->fireEvent( 'registrationUserDisabled', array( $this ) );
    }

    /**
     * Change the E-Mail from an user
     * The user get a mail for the mail confirmation if the new mail is different as the current
     *
     * @param \QUI\Users\User $User - User
     * @param String $email - new mail
     */
    public function changeMailFromUser($User, $email)
    {
        if ( $email == $User->getAttribute('email') ) {
            return;
        }

        if ( !Orthos::checkMailSyntax( $email ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/system',
                    'exception.not.correct.email'
                )
            );
        }

        // check if the new mail exists in the system
        if ( \QUI::getUsers()->existEmail( $email ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/system',
                    'exception.user.register.email.exists'
                )
            );
        }


        // if the quiqqer.intranet.new.email is the same,
        // we only send a new activasion and dont set a new hash
        $newMail  = $User->getAttribute( 'quiqqer.intranet.new.email' );
        $mailhash = $User->getAttribute( 'quiqqer.intranet.new.email.hash' );


        if ( empty( $newMail ) || empty( $mailhash ) || $newMail != $email )
        {
            $hash = Orthos::getPassword();

            $User->setAttribute( 'quiqqer.intranet.new.email' , $email );
            $User->setAttribute( 'quiqqer.intranet.new.email.hash' , $hash );
            $User->save();
        }

        $this->_sendNewEMailActivasion( $User );
    }

    /**
     * Set the new email for the user
     *
     * @param {String|Integer} $user - username, user-id
     * @param {String} $hash - hash to activate the new mail
     */
    public function setNewEmail($user, $hash)
    {
        $User = $this->_getUser( $user );

        $newMail  = $User->getAttribute( 'quiqqer.intranet.new.email' );
        $mailHash = $User->getAttribute( 'quiqqer.intranet.new.email.hash' );


        if ( !$newMail || !Orthos::checkMailSyntax( $newMail ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.wrong.hash'
                )
            );
        }

        // check if the new mail exists in the system
        if ( \QUI::getUsers()->existEmail( $newMail ) )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/system',
                    'exception.user.register.email.exists'
                )
            );
        }


        if ( $mailHash != $hash )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.wrong.hash'
                )
            );
        }

        $User->setAttribute( 'email', $newMail );
        $User->removeAttribute( 'quiqqer.intranet.new.email' );
        $User->removeAttribute( 'quiqqer.intranet.new.email.hash' );

        $User->save( \QUI::getUsers()->getSystemUser() );
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
        if ( !isset( $Site ) || !$Site ) {
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

        if ( !isset( $Site ) || !$Site ) {
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
     * Send an disable mail
     * it starts the disable process
     *
     * @param \QUI\Users\User $User
     * @param \QUI\Projects\Site $Site - [optional]
     */
    public function sendDisableMail(\QUI\Users\User $User, $Site=false)
    {
        $Project = $this->_getProject();
        $Locale  = \QUI::getLocale();
        $Engine  = \QUI::getTemplateManager()->getEngine();
        $Package = \QUI::getPackageManager()->getInstalledPackage( 'quiqqer/intranet' );

        if ( !isset( $Site ) || !$Site ) {
            $Site = $this->_getRegSite();
        }

        // disable event
        \QUI::getEvents()->fireEvent( 'registrationUserDisable', array( $this ) );

        // create new disable link
        $hashLifetime = $Package->getConfig()->get( 'disable', 'hashLifetime' );
        $hashtime     = $User->getAttribute( 'quiqqer.intranet.disable.time' );

        $hash = $User->getAttribute( 'quiqqer.intranet.disable.hash' );

        // wenn hash abgelaufen, neuen hash setzen
        if ( !$hashtime || $hashLifetime < time() - $hashtime )
        {
            $hash = Orthos::getPassword();

            $User->setAttribute( 'quiqqer.intranet.disable.hash', $hash );
            $User->setAttribute( 'quiqqer.intranet.disable.time', time() );
            $User->save();
        }

        /**
         * Disable Mail
         */
        $Engine->assign(array(
            'Project' => $Project,
            'Site'    => $Site,
            'User'    => $User
        ));

        $disable_link = $Project->getVHost( true ) . $Site->getUrl(array(
            'uid'  => $User->getId(),
            'hash' => $hash,
            'type' => 'disable'
        ), true);



        // Mail vars
        $MAILFromText = '';
        $MailSubject  = '';

        $Locale  = \QUI::getLocale();
        $project = $Project->getAttribute('name');

        // schauen ob es übersetzungen dafür gibt
        if ( $Locale->exists('project/'. $project, 'intranet.disable.MAILFromText') )
        {
            $MAILFromText = $Locale->get('project/'. $project, 'intranet.disable.MAILFromText');
        } else
        {
            $MAILFromText = $Locale->get('quiqqer/intranet', 'mail.disable.MAILFromText');
        }


        if ( $Locale->exists('project/'. $project, 'intranet.disable.Subject') )
        {
            $MailSubject = $Locale->get('project/'. $project, 'intranet.disable.MailSubject');
        } else
        {
            $MailSubject = $Locale->get('quiqqer/intranet', 'mail.disable.MailSubject');
        }

        $MailBody = \QUI::getLocale()->get(
            'quiqqer/intranet',
            'mail.disable.Body',
            array(
                'disable_link' => $disable_link
            )
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
                    'exception.send.disable.mail.fail'
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
        $hash    = Orthos::getPassword();

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
        $newpass = Orthos::getPassword();

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
            Orthos::getPassword()
        );

        $User->save( $Users->getSystemUser() );

        \QUI::getMessagesHandler()->addSuccess(
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'message.send.new.password.successfully'
            )
        );
    }

    /**
     * Sends an information mail to an admin about a registration
     *
     * @param \QUI\Users\User $User
     */
    protected function _sendInformationRegistrationMailTo(\QUI\Users\User $User)
    {
        $Package = \QUI::getPackageManager()->getInstalledPackage( 'quiqqer/intranet' );
        $Config  = $Package->getConfig();

        $email = $Config->get('registration', 'sendInfoMailOnRegistrationTo');

        if ( !$email ) {
            return;
        }

        if ( !Orthos::checkMailSyntax( $email ) ) {
            return;
        }

        // mail subject
        $subject = \QUI::getLocale()->get(
            'quiqqer/intranet',
            'mail.registration.admin.info.subject'
        );

        if ( $User->getAttribute('quiqqer.intranet.googleid') )
        {
            $subject = \QUI::getLocale()->get(
                'quiqqer/intranet',
                'mail.registration.admin.info.subject.social',
                array( 'social' => 'Google SignIn' )
            );
        }

        if ( $User->getAttribute('quiqqer.intranet.facebookid') )
        {
            $subject = \QUI::getLocale()->get(
                'quiqqer/intranet',
                'mail.registration.admin.info.subject.social',
                array( 'social' => 'Facebook SignIn' )
            );
        }


        $useragent = '';

        if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
            $useragent = $_SERVER['HTTP_USER_AGENT'];
        }

        // geodaten wenn vorhanden
        $geopIpData = "".
                 ( isset( $_SERVER["GEOIP_COUNTRY_CODE"] ) ? $_SERVER["GEOIP_COUNTRY_CODE"] : '')." ".
                 ( isset( $_SERVER["GEOIP_COUNTRY_NAME"] ) ? $_SERVER["GEOIP_COUNTRY_NAME"] : '').", ".
                 ( isset( $_SERVER["GEOIP_CITY"] ) ? utf8_encode($_SERVER["GEOIP_CITY"]) : '')." (".
                 ( isset( $_SERVER["GEOIP_LATITUDE"] ) ? $_SERVER["GEOIP_LATITUDE"] : '')." / ".
                 ( isset( $_SERVER["GEOIP_LONGITUDE"] ) ? $_SERVER["GEOIP_LONGITUDE"] : '')." )\n";

        // userdata
        $attributes = $User->getAttributes();
        $data       = "";

        foreach ( $attributes as $key => $value )
        {
            if ( !is_string( $value ) ) {
                continue;
            }

            if ( empty( $value ) ) {
                continue;
            }

            $data .= $key .': '. $value ."\n";
        }


        $body = \QUI::getLocale()->get(
            'quiqqer/intranet',
            'mail.registration.admin.info.body',
            array(
                'user_name'   => $User->getName(),
                'user_id'     => $User->getId(),
                'user_agent'  => $useragent,
                'user_ip'     => \QUI\Utils\System::getClientIP(),
                'geo_ip_data' => $geopIpData,
                'data'        => $data
            )
        );


        \QUI::getMailManager()->send( $email, $subject, $body );
    }

    /**
     * Send the user an email activasion for its new email
     *
     * @param \QUI\Users\User $User
     */
    protected function _sendNewEMailActivasion(\QUI\Users\User $User)
    {
        $Project = $this->_getProject();
        $RegSite = $this->_getRegSite();

        $Users   = \QUI::getUsers();
        $Engine  = \QUI::getTemplateManager()->getEngine();


        $emailHash = $User->getAttribute( 'quiqqer.intranet.new.email.hash' );
        $newEmail  = $User->getAttribute( 'quiqqer.intranet.new.email' );

        // create mail
        $MAILFromText = '';
        $MailSubject  = '';
        $project      = $Project->getName();

        // schauen ob es übersetzungen dafür gibt
        if ( \QUI::getLocale()->exists('project/'. $project, 'intranet.new.email.MAILFromText') )
        {
            $MAILFromText = \QUI::getLocale()->get(
                'project/'. $project,
                'intranet.new.email.MAILFromText'
            );

        } else
        {
            $MAILFromText = \QUI::getLocale()->get(
                'quiqqer/intranet',
                'intranet.new.email.MAILFromText'
            );
        }


        if ( \QUI::getLocale()->exists('project/'. $project, 'intranet.new.email.MailSubject') )
        {
            $MailSubject = \QUI::getLocale()->get(
                'project/'. $project,
                'intranet.new.email.MailSubject'
            );

        } else
        {
            $MailSubject = \QUI::getLocale()->get(
                'quiqqer/intranet',
                'intranet.new.email.MailSubject'
            );
        }


        $activasion_link = $Project->getVHost( true ) . $RegSite->getUrl(array(
            'uid'  => $User->getId(),
            'hash' => $emailHash,
            'type' => 'newMail'
        ), true);


        $MailBody = \QUI::getLocale()->get(
            'quiqqer/intranet',
            'intranet.new.email.Body',
            array(
                'username' => $User->getName(),
                'uid'      => $User->getId(),
                'hash'     => $emailHash,
                'activasion_link' => $activasion_link
            )
        );


        // send mail
        $Mail = new \QUI\Mail\Mailer();

        $Mail->setProject( $this->_getProject() );
        $Mail->setSubject( $MailSubject );
        $Mail->setFromName( $MAILFromText );
        $Mail->addRecipient( $newEmail );
        $Mail->setBody( $MailBody );

        $Mail->Template->setAttributes(array(
            'Project' => $Project,
            'Site'    => $RegSite,
            'User'    => $User,
            'hash'    => $emailHash
        ));

        if ( !$Mail->send() )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.send.new.email.fail'
                )
            );
        }


        \QUI::getMessagesHandler()->addSuccess(
            \QUI::getLocale()->get(
                'quiqqer/intranet',
                'message.send.new.email.successfully'
            )
        );
    }
}

