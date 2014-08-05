
/**
 * Social Login via Google
 *
 * @author www.pcsg.de (Henning Leutz)
 * @module package/quiqqer/intranet/social/Google
 *
 * @event signInBegin
 * @event signInEnd
 * @event onAuth [ {self}, {Object} data ]
 */

define([

    'qui/QUI',
    'qui/controls/Control',
    'qui/Locale',

    'css!package/quiqqer/intranet/social/Google.css'

], function(QUI, QUIControl, QUILocale)
{
    "use strict";

    return new Class({

        Extends : QUIControl,
        Type    : 'package/quiqqer/intranet/social/Google',

        options : {
            name : 'google'
        },

        initialize : function(options)
        {
            this.parent( options );
        },

        /**
         * Creates the DOMNode Element
         *
         * @return {DOMNode}
         */
        create : function()
        {
            var self = this;

            this.$Elm = new Element('div', {
                'class' : 'qui-box quiqqer-google-login',
                events :
                {
                    click : function() {
                        self.login();
                    }
                }
            });

            return this.$Elm;
        },

        /**
         * Loged the user in
         */
        login : function()
        {
            if ( typeof QUIQQER_USER !== 'undefined' &&
                 parseInt( QUIQQER_USER.id ) )
            {
                return;
            }

            var self = this;

            this.googleSignIn(function(authResult)
            {
                gapi.auth.setToken( authResult );

                gapi.client.load('oauth2', 'v2', function()
                {
                    var request = gapi.client.oauth2.userinfo.get();

                    request.execute(function(obj)
                    {
                        if ( obj.code == 404 )
                        {
                            require(['MessageHandler'], function(MH)
                            {
                                MH.addError(
                                    QUILocale.get(
                                        'plugins/intranet',
                                        'google.registration.error'
                                    )
                                );
                            });

                            return;
                        }

                        var socialData = {
                            email     : obj.email,
                            name      : obj.name,
                            gender    : obj.gender,
                            lastname  : obj.family_name,
                            firstname : obj.given_name,
                            googleid  : obj.id,
                            locale    : obj.locale,
                            picture   : obj.picture,
                            token     : authResult
                        };

                        self.fireEvent( 'auth', [ self, socialData ] );
                    });
                });
            });
        },

        /**
         * Sign in via google, get the data via google
         *
         * @param {Function} callback
         */
        googleSignIn : function(callback)
        {
            if ( typeof QUIQQER_USER !== 'undefined' &&
                 parseInt( QUIQQER_USER.id ) )
            {
                return;
            }

            this.fireEvent( 'signInBegin' );

            var self = this;

            if ( typeof gapi === 'undefined' )
            {
                this.loadGoogle(function() {
                    self.googleSignIn( callback );
                });

                return;
            }

            gapi.auth.signIn({
                callback : function(authResult)
                {
                    self.fireEvent( 'signInEnd' );

                    if ( !authResult.access_token )
                    {
                        QUI.getMessageHandler(function(MH) {
                            MH.addError( authResult.error );
                        });

                        return;
                    }

                    if ( typeof callback !== 'undefined' ) {
                        callback( authResult );
                    }
                },

                clientid     : "675767772092-6boaf9imuk5p5skcl9oh83su4mlc7it2.apps.googleusercontent.com",
                cookiepolicy : "single_host_origin",
                accesstype   : "offline",

                requestvisibleactions : "http://schemas.google.com/AddActivity",

                scope : 'https://www.googleapis.com/auth/plus.login '+
                        'https://www.googleapis.com/auth/userinfo.email '+
                        'https://www.googleapis.com/auth/userinfo.profile'
            });
        },

        /**
         * load the google api
         *
         * @param {Function} callback - [optional]
         */
        loadGoogle : function(callback)
        {
            try
            {
                window.gPlusSigninCallback = function()
                {
                    if ( typeOf( callback ) === 'function' ) {
                        callback();
                    }
                };


                if ( !document.id( 'gplusapi' ) )
                {
                    var po = document.createElement( 'script' );
                        po.type  = 'text/javascript';
                        po.async = true;
                        po.src   = '//apis.google.com/js/client:plusone.js?onload=gPlusSigninCallback';
                        po.id    = 'gplusapi';

                    var s = document.getElementsByTagName( 'script' )[0];
                        s.parentNode.insertBefore( po, s );
                }

            } catch ( e )
            {
                if ( typeOf( callback ) === 'function' ) {
                    callback();
                }
            }
        }
    });
});