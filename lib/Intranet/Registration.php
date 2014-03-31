<?php

/**
 * This file contains \QUI\Intranet\Registration;
 */

namespace QUI\Intranet;

/**
 * QUIQQER Registration
 *
 * @author www.pcsg.de (Henning Leutz)
 */

class Registration
{
    /**
     * Register a user over $_REQUEST params
     *
     * $_REQUEST['nickname']
     * $_REQUEST['email']
     * $_REQUEST['password']
     * $_REQUEST['password2']
     */
    public function register()
    {

        if ( !isset( $_REQUEST['nickname'] ) )
        {
            // Bitte geben Sie einen Benutzernamen an
            throw new PException(
                PCSG::getLocale()->get(
                    'plugin/intranet',
                    'except.enter.username'
                )
            );
        }

        if ( !isset( $_REQUEST['email'] ) )
        {
            // Bitte geben Sie eine E-Mail Adresse an
            throw new PException(
                PCSG::getLocale()->get(
                    'plugin/intranet',
                    'except.enter.mail'
                )
            );
        }

        if ( !PT_Orthos::checkMailSyntax( $_REQUEST['email'] ) )
        {
            throw new PException(
                PCSG::getLocale()->get(
                    'plugin/intranet',
                    'except.enter.correct.mail'
                )
            );
        }

        // Passwort Prüfung
        if ( !isset( $_REQUEST['password'] ) || empty( $_REQUEST['password'] ) )
        {
            // Bitte geben Sie ein Passwort ein
            throw new PException(
                PCSG::getLocale()->get(
                    'plugin/intranet',
                    'except.enter.pw'
                )
            );
        }

        if ( !isset( $_REQUEST['password2'] ) )
        {
            // Bitte bestätigen Sie das Passwort
            throw new PException(
                PCSG::getLocale()->get(
                    'plugin/intranet',
                    'except.confirm.pw'
                )
            );
        }

        if ( $_REQUEST['password'] != $_REQUEST['password2'] )
        {
            // Beide Passwörter sind nicht identisch
            throw new PException(
                PCSG::getLocale()->get(
                    'plugin/intranet',
                    'except.different.pw'
                )
            );
        }

        if ( !isset( $_REQUEST['agbs'] ) && $_REQUEST['agbs'] )
        {
            // Bitte akzeptieren Sie die AGBs
            throw new PException(
                PCSG::getLocale()->get(
                    'plugin/intranet',
                    'except.accept.agb'
                )
            );
        }

        $Users = PCSG::getUsers();

        if ( $Users->checkUsername( $_REQUEST['nickname'] ) )
        {
            // Bitte verwenden Sie einen andere Benutzernamen
            throw new PException(
                PCSG::getLocale()->get(
                    'plugin/intranet',
                    'except.different.username'
                )
            );
        }

        $Plugin  = \QUI::getPlugins()->get( 'quiqqer/intranet' );
        $groupid = (int)$Plugin->getSettings('registration', 'standardGroup');


        $langs    = PCSG::availableLanguages();
        $newLang  = PCSG::getLocale()->getCurrent();
        $userLang = 'en';

        if ( in_array( $newLang, $langs ) ) {
            $userLang = $newLang;
        }

        $User = $Users->register(array(
            'username'  => $_REQUEST['nickname'],
            'password'  => $_REQUEST['password'],
            'email'     => $_REQUEST['email'],
            'usergroup' => $groupid,
            'lang'      => $userLang
        ));

        if ( $Plugin->getSettings('registration', 'sendMail') ) {
            $this->sendRegistrationMail( $User );
        }

        return $User;
    }

    /**
     * Send a registration mail to the user
     *
     * @param \QUI\Users\User $User
     * @param \QUI\Projects\Site $Site - [optional]
     */
    public function sendRegistrationMail(\QUI\Users\User $User, \QUI\Projects\Site $Site=false)
    {
        $Project = \QUI::getProjects()->get();
        $Locale  = \QUI::getLocale();

        $project = $Project->getAttribute('name');

        if ( !$Site )
        {
            $list = $Project->getSites(array(
                'where' => array(
                    'type' => 'intranet/registration'
                ),
                'limit' => 1
            ));

            if ( !isset( $list[0] ) )
            {
                throw new PException(
                    PCSG::getLocale()->get(
                        'plugin/intranet',
                        'except.activated.mail.fail'
                    )
                );
            }

            $Site = $list[0];
        }



    }

}

