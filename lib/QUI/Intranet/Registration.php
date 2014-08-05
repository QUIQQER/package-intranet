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
 */

class Registration extends \QUI\QDOM
{
    /**
     * constructor
     * @param array $params
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
    public function socialRegister($social, $socialData)
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

        $User->setAttribute( '', $value );
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
     * Sends a forget password mail
     *
     * @param Integer|String $user
     * @param \QUI\Projects\Project $Project
     */
    public function forgetPassword($user)
    {

    }

    /**
     * Create a new password for an user
     *
     * @param Integer|String $uid
     * @param String $hash - create hash
     */
    public function createNewPassword($uid, $hash)
    {

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
        $Engine  = \QUI::getTemplateManager()->getEngine();

        $project = $Project->getAttribute('name');

        // if no site, find a registration site
        if ( !$Site ) {
            $Site = $this->_getRegSite();
        }

        // create registration mail
        $reg_url  = $Project->getVHost( true ) . URL_DIR . $Site->getUrlRewrited() .'?';
        $reg_url .= 'code='. $User->getAttribute('activation') .'&';
        $reg_url .= 'nickname='. $User->getName();

        $Engine->assign(array(
            'Project'  => $Project,
            'Site'     => $Site,
            'User'     => $User,
            'code'     => $User->getAttribute('activation'),
            'nickname' => $User->getName(),
            'reg_url'  => $reg_url,
            'body'     => \QUI::getLocale()->get(
                'quiqqer/intranet',
                'mail.registration.Body',
                array( 'registration_url' => $reg_url )
            )
        ));

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

        // send registration mail
        $Mail = new \QUI\Mail(array(
            'MAILFromText' => $MAILFromText
        ));

        $body = $Engine->fetch( OPT_DIR .'quiqqer/intranet/mails/activation.html' );

        $result = $Mail->send(array(
             'MailTo'  => $User->getAttribute('email'),
             'Subject' => $MailSubject,
             'Body'    => $body,
             'IsHTML'  => true
        ));


        if ( !$result )
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


        $Mail = new \QUI\Mail(array(
            'MAILFromText' => $MAILFromText
        ));

        $body = $Engine->fetch( OPT_DIR .'quiqqer/intranet/mails/activation_success.html' );


        $send = $Mail->send(array(
             'MailTo'  => $User->getAttribute('email'),
             'Subject' => $MailSubject,
             'Body'    => $body,
             'IsHTML'  => true
        ));

        if ( !$send )
        {
            throw new \QUI\Exception(
                \QUI::getLocale()->get(
                    'quiqqer/intranet',
                    'exception.send.activation.successful.mail.fail'
                )
            );
        }
    }
}
