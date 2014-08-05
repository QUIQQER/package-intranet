
/**
 * Social Login via Facebook
 *
 * @author www.pcsg.de (Henning Leutz)
 * @module package/quiqqer/intranet/social/Facebook
 *
 * @event signInBegin
 * @event signInEnd
 * @event onAuth [ {self}, {Object} data ]
 */

define([

    'qui/QUI',
    'qui/controls/Control',
    'qui/Locale',

    'css!package/quiqqer/intranet/social/Facebook.css'

], function(QUI, QUIControl, QUILocale)
{
    "use strict";

    return new Class({

        Extends : QUIControl,
        Type    : 'package/quiqqer/intranet/social/Facebook',

        options : {
            name  : 'facebook',
            appId : '1516174485267386'
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
                'class' : 'qui-box quiqqer-facebook-login',
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

            this.facebookSignIn(function()
            {
                FB.getLoginStatus(function(response)
                {
                    if ( response.status === 'connected' )
                    {
                        var uid         = response.authResponse.userID,
                            accessToken = response.authResponse.accessToken;

                        FB.api('/me', function(response)
                        {
                            var socialData = {
                                email      : response.email,
                                name       : response.name,
                                gender     : response.gender,
                                lastname   : response.last_name,
                                firstname  : response.first_name,
                                facebookid : response.id,
                                locale     : response.locale,
                                link       : response.link,
                                token      : accessToken
                            };

                            self.fireEvent( 'auth', [ self, socialData ] );
                        });

                        return;
                    }

                    require(['MessageHandler'], function(MH)
                    {
                        MH.addError(
                            QUILocale.get(
                                'plugins/intranet',
                                'facebook.registration.error'
                            )
                        );
                    });

                }, true);
            });
        },


        /**
         * Sign in via facebook, get the data via facebook
         *
         * @param {Function} callback
         */
        facebookSignIn : function(callback)
        {
            if ( typeof QUIQQER_USER !== 'undefined' &&
                 parseInt( QUIQQER_USER.id ) )
            {
                return;
            }

            this.fireEvent( 'signInBegin' );

            var self = this;

            if ( typeof FB === 'undefined' )
            {
                this.loadFacebook(function() {
                    self.facebookSignIn( callback );
                });

                return;
            }

            FB.login(function(response)
            {
                self.fireEvent( 'signInEnd' );

                callback();
            }, {
                scope: 'email'
            });
        },

        /**
         * load the facebook api
         *
         * @param {Function} callback - [optional]
         */
        loadFacebook : function(callback)
        {
            var self = this;

            try
            {
                window.fbAsyncInit = function()
                {
                    if ( typeOf( callback ) === 'function' ) {
                        callback();
                    }
                };

                if ( !document.id( 'fb-root' ) ) {
                    new Element( 'div#fb-root' ).inject( document.body );
                }

                // fb js
                if ( !document.getElementById( 'facebook-jssdk' ) )
                {
                    var js,
                        fjs = document.getElementsByTagName( 'script' )[0];

                    js     = document.createElement( 'script' );
                    js.id  = 'facebook-jssdk';
                    js.src = "//connect.facebook.net/de_DE/all.js#xfbml=1&appId="+ this.getAttribute('appId');

                    fjs.parentNode.insertBefore( js, fjs );
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