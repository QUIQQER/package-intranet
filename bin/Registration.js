
/**
 * Intranet registration
 * A user can register an account to the system
 *
 * @module package/quiqqer/intranet/bin/Registration
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onRegisterSuccess
 */

define('package/quiqqer/intranet/bin/Registration', [

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

    var lg = 'quiqqer/intranet';

    return new Class({

        Extends : QUIControl,
        Type    : 'package/quiqqer/intranet/bin/Registration',

        Binds : [
            '$onImport',
            '$onMailInputBlur'
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
         * @param {Object} self - package/quiqqer/intranet/Registration
         * @param {HTMLElement} Elm
         */
        $onImport : function(self, Elm)
        {
            //var self = this;

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

            this.$PWSecContainer = Elm.getElement( '#reg-pwsecurity' );


            // send button
            new QUIButton({
                name   : 'register-button',
                text   : Locale.get( 'quiqqer/intranet', 'registration.btn.submit' ),
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

            SecField.inject( this.$PWSecContainer );
            SecField.bindInput( this.$Pass1 );

            // form submit
            Elm.getElements( 'form' ).addEvent('submit', function(event)
            {
                event.stop();
                self.submit();
            });

            this.$Mail1.addEvent( 'blur', this.$onMailInputBlur );

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

            if ( this.$Mail1.value === '' )
            {
                QUI.getMessageHandler(function(MH)
                {
                    MH.addAttention(
                        Locale.get( lg, 'exception.error.email.empty' ),
                        self.$Mail1
                    );
                });

                this.$Mail1.focus();
                return;
            }

            if ( this.$Pass1.value === '' )
            {
                QUI.getMessageHandler(function(MH)
                {
                    MH.addAttention(
                        Locale.get( lg, 'exception.error.password.empty' ),
                        self.$Pass1
                    );
                });

                this.$Pass1.focus();
                return;
            }

            if ( this.$Mail1.value != this.$Mail2.value )
            {
                QUI.getMessageHandler(function(MH)
                {
                    MH.addAttention(
                        Locale.get( lg, 'exception.error.emails.unequal' ),
                        self.$Mail2
                    );
                });

                this.$Mail2.focus();
                return;
            }

            if ( this.$Pass1.value != this.$Pass2.value )
            {
                QUI.getMessageHandler(function(MH)
                {
                    MH.addAttention(
                        Locale.get( lg, 'exception.error.passwords.unequal' ),
                        self.$Pass2
                    );
                });

                this.$Pass2.focus();

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
         * event : on email input blur
         */
        $onMailInputBlur : function()
        {
            var self = this;

            this.isUsernameAvailable( this.$Mail1.value, function(result)
            {
                if ( result ) {
                    return;
                }

                QUI.getMessageHandler(function(MH)
                {
                    MH.addAttention(
                        Locale.get( lg, 'exception.mail.not.available' ),
                        self.$Mail1
                    );

                    self.$Mail1.focus();
                });
            });
        },

        /**
         * Registration
         *
         * @param {String} email
         * @param {String} password
         * @param {Object} data
         * @param {Function} [callback]
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
                            Locale.get( lg, 'message.error.user.not.allowed' ),
                            self.$Pass2
                        );
                    });

                    self.Loader.hide();
                    return;
                }


                Ajax.post('package_quiqqer_intranet_ajax_user_register', function(result)
                {
                    self.getElm().set(
                        'html',

                        '<div class="messages-message message-error">'+
                            result +
                        '</div>'
                    );

                    self.fireEvent( 'registerSuccess' );

                    document.body.getElements( '.content-short' ).set( 'html', '' );
                    

                    if ( typeof callback !== 'undefined' ) {
                        callback( result );
                    }

                    self.Loader.hide();

                }, {
                    email     : email,
                    password  : password,
                    data      : JSON.decode( data ),
                    'package' : 'quiqqer/intranet',
                    onError   : function() {
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
                            project    : JSON.encode({
                                name : QUIQQER_PROJECT.name,
                                lang : QUIQQER_PROJECT.lang
                            }),
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
                        project    : JSON.encode({
                            name : QUIQQER_PROJECT.name,
                            lang : QUIQQER_PROJECT.lang
                        }),
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
                project   : JSON.encode({
                    name : QUIQQER_PROJECT.name,
                    lang : QUIQQER_PROJECT.lang
                }),
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
                project    : JSON.encode({
                    name : QUIQQER_PROJECT.name,
                    lang : QUIQQER_PROJECT.lang
                }),
                'package'  : 'quiqqer/intranet'
            });
        },

        /**
         * is the user loged in?
         *
         * @return {Boolean}
         */
        isLogedIn : function()
        {
            if ( typeof QUIQQER_USER === 'undefined' ) {
                return false;
            }

            return ( "id" in QUIQQER_USER && parseInt( QUIQQER_USER.id ) );
        },

        /**
         * Check the e-mail and username, if is the str usable as an username
         *
         * @param {String} str - string to test
         * @param {Function} callback - Callback function -> callback( true || false )
         */
        isUsernameAvailable : function(str, callback)
        {
            if ( str === '' )
            {
                callback( false );
                return;
            }

            Ajax.get([
                'package_quiqqer_intranet_ajax_user_existsUsername',
                'package_quiqqer_intranet_ajax_user_existsMail'
            ], function(usernameExists, mailExists)
            {
                if ( usernameExists || mailExists  )
                {
                    callback( false );
                    return;
                }

                callback( true );

            }, {
                username  : str,
                email     : str,
                'package' : 'quiqqer/intranet'
            });
        }
    });

});