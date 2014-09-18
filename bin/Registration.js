
/**
 * Intranet registration
 * A user can register an account to the system
 *
 * @module package/quiqqer/intranet/bin/Registration
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onRegisterSuccess
 */

define([

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/loader/Loader',
    'qui/controls/buttons/Button',
    'qui/controls/utils/Background',
    'qui/controls/utils/PasswordSecurity',
    'Ajax',
    'Locale'

], function(QUI, QUIControl, QUILoader, QUIButton, QUIBackground, QUIPwSec, Ajax, Locale)
{
    "use strict";

    return new Class({

        Extends : QUIControl,
        Type    : 'package/quiqqer/intranet/bin/Registration',

        Binds : [
            '$onImport'
        ],

        initialize : function(options)
        {
            this.parent( options );

            this.Loader = new QUILoader();

            this.$Mail1 = null;
            this.$Mail2 = null;
            this.$Pass1 = null;
            this.$Pass2 = null;
            this.$AGB   = null;

            this.$PWSecContainer = null;

            this.addEvents({
                onImport : this.$onImport
            });
        },

        /**
         * on import
         *
         * @param {package/quiqqer/intranet/Registration} self
         * @param {DOMNode} Elm
         */
        $onImport : function(self, Elm)
        {
            var self = this;

            this.Loader.inject( Elm );
            this.Loader.show();

            if ( !Elm.getElement( '#reg-email' ) )
            {
                this.Loader.hide();
                return;
            }

            var Hide = Elm.getElement( '.package-intranet-registration-hide' );
                Hide.setStyle( 'display', 'inline' );

            // elements
            this.$Mail1 = Elm.getElement( '#reg-email' );
            this.$Mail2 = Elm.getElement( '#reg-email2' );
            this.$Pass1 = Elm.getElement( '#reg-password' );
            this.$Pass2 = Elm.getElement( '#reg-password2' );
            this.$AGB   = Elm.getElement( '#reg-agb-privacy' );

            this.$PWSecContainer = Elm.getElement( '#reg-pwsecurity' )


            // send button
            new QUIButton({
                name   : 'register-button',
                text   : 'Jetzt registrieren',
                styles : {
                    display : 'block',
                    'float' : 'none',
                    margin  : '0 auto',
                    width   : '50%'
                },
                events :
                {
                    onClick : function() {
                        self.submit();
                    }
                }
            }).inject( Elm.getElement('.package-intranet-registration-submit') );


            // agb
            this.$AGB.addEvents({
                change : function()
                {
                    if ( this.checked )
                    {
                        Hide.setStyle( 'display', 'none' );
                    } else
                    {
                        Hide.setStyle( 'display', 'inline' );
                    }
                }
            });

            this.$AGB.fireEvent( 'change' );


            // pw security
            var SecField = new QUIPwSec({
                styles : {
                    clear : 'both',
                    width : '100%'
                }
            });

            SecField.inject( this.$PWSecContainer )
            SecField.bindInput( this.$Pass1 );

            // form submit
            Elm.getElements( 'form' ).addEvent('submit', function(event) {
                event.stop();
            });

            // qui parsing
            QUI.parse(Elm, function()
            {
                var socialList = Elm.getElements( '.register-social-login [data-quiid]' ),
                    controls   = socialList.map(function(Elm) {
                        return QUI.Controls.getById( Elm.get('data-quiid') );
                    });

                // social auth events - social register
                for ( var i = 0, len = controls.length; i < len; i++ )
                {
                    controls[ i ].addEvent( 'onAuth', function(Social, params) {
                        self.socialRegister( Social.getAttribute('name'), params );
                    });

                    controls[ i ].getElm().setStyles({
                        display : 'inline-block',
                        'float' : 'none'
                    });
                }

                self.Loader.hide();
            });
        },

        /**
         * submit form and register the mail
         */
        submit : function()
        {
            var self = this;

            if ( this.$Mail1.value == '' )
            {
                QUI.getMessageHandler(function(MH)
                {
                    MH.addAttention(
                        Locale.get( 'quiqqer/intranet', 'message.error.email.empty' ),
                        self.$Mail1
                    );
                });

                return;
            }

            if ( this.$Pass1.value == '' )
            {
                QUI.getMessageHandler(function(MH)
                {
                    MH.addAttention(
                        Locale.get( 'quiqqer/intranet', 'message.error.password.empty' ),
                        self.$Pass1
                    );
                });

                return;
            }

            if ( this.$Mail1.value != this.$Mail2.value )
            {
                QUI.getMessageHandler(function(MH)
                {
                    MH.addAttention(
                        Locale.get( 'quiqqer/intranet', 'message.error.emails.unequal' ),
                        self.$Mail2
                    );
                });

                return;
            }

            if ( this.$Pass1.value != this.$Pass2.value )
            {
                QUI.getMessageHandler(function(MH)
                {
                    MH.addAttention(
                        Locale.get( 'quiqqer/intranet', 'message.error.passwords.unequal' ),
                        self.$Pass2
                    );
                });

                return;
            }

            this.register(
                this.$Mail1.value,
                this.$Pass1.value,
                {},
                function() {

                }
            );
        },

        /**
         * Registration
         *
         * @param {String} email
         * @param {String} password
         * @param {Object} data
         * @param {Function} callback
         */
        register : function(email, password, data, callback)
        {
            var self = this;

            this.Loader.show();


            this.isItRegistered(email, function(result)
            {
                // user is registered
                if ( result )
                {
                    QUI.getMessageHandler(function(MH)
                    {
                        MH.addAttention(
                            Locale.get( 'quiqqer/intranet', 'message.error.user.not.allowed' ),
                            self.$Pass2
                        );
                    });

                    self.Loader.hide();
                    return;
                }


                Ajax.post('package_quiqqer_intranet_ajax_user_register', function(result)
                {
                    self.getElm().set( 'html', result );
                    self.fireEvent( 'registerSuccess' );
                    self.Loader.hide();

                }, {
                    email     : email,
                    password  : password,
                    data      : JSON.decode( data ),
                    'package' : 'quiqqer/intranet',
                    onError   : function(Exception) {
                        self.Loader.hide();
                    }
                });
            });
        },

        /**
         * registration with social media signin
         *
         * @param {String} socialType - Social media type
         * @param {Object} socialData - Social media user data
         */
        socialRegister : function(socialType, socialData)
        {
            var self = this;

            this.Loader.show();

            this.isItRegistered(socialData.email, function(registered)
            {
                // check if social access exists
                self.hasSocialAccess(socialData.email, socialType, function(socialAccess)
                {
                    if ( !registered || !socialAccess )
                    {
                        // register user with social media
                        Ajax.post('package_quiqqer_intranet_ajax_user_socialRegister', function(register)
                        {
                            if ( register )
                            {
                                self.socialRegister( socialType, socialData );
                            } else
                            {
                                self.Loader.show();
                            }

                        }, {
                            socialType : socialType,
                            socialData : JSON.encode( socialData ),
                            project    : QUIQQER_PROJECT.name,
                            'package'  : 'quiqqer/intranet'
                        });

                        return;
                    }

                    // login
                    if ( self.isLogedIn() )
                    {
                        window.location.reload();
                        return;
                    }

                    Ajax.post('package_quiqqer_intranet_ajax_user_socialLogin', function()
                    {
                        window.location.reload();
                    }, {
                        token      : JSON.encode( socialData.token ),
                        socialType : socialType,
                        project    : QUIQQER_PROJECT.name,
                        'package'  : 'quiqqer/intranet'
                    });

                });
            });
        },

        /**
         * is the user registered?
         *
         * @param {String} email
         * @param {Function} callback
         */
        isItRegistered : function(email, callback)
        {
            callback = callback || function(){};

            Ajax.get('package_quiqqer_intranet_ajax_user_isRegistered', callback, {
                email     : email,
                project   : QUIQQER_PROJECT.name,
                'package' : 'quiqqer/intranet'
            });
        },

        /**
         * Check if the user has social access
         *
         * @param {String} email
         * @param {String} socialType
         * @param {Function} callback
         */
        hasSocialAccess : function(email, socialType, callback)
        {
            callback = callback || function(){};

            Ajax.get('package_quiqqer_intranet_ajax_user_hasSocialAccess', callback, {
                email      : email,
                socialType : socialType,
                project    : QUIQQER_PROJECT.name,
                'package'  : 'quiqqer/intranet'
            });
        },

        /**
         * is the user loged in?
         *
         * @return {Bool}
         */
        isLogedIn : function()
        {
            if ( typeof QUIQQER_USER === 'undefined' ) {
                return false;
            }

            if ( "id" in QUIQQER_USER && parseInt( QUIQQER_USER.id ) ) {
                return true;
            }

            return false;
        }
    });

});