
/**
 * Social Login via Facebook
 *
 * @module package/quiqqer/intranet/bin/social/Facebook
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/Locale
 * @require css!package/quiqqer/intranet/bin/social/Facebook.css
 *
 * @event onSignInBegin [ {self} ]
 * @event onSignInEnd [ {self} ]
 * @event onSignInError [ {self}, {Object} authResult ]
 * @event onLoginBegin [ {self} ]
 * @event onAuth [ {self}, {Object} data ]
 */

define('package/quiqqer/intranet/bin/social/Facebook', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/Locale',
    'Ajax',

    'css!package/quiqqer/intranet/bin/social/Facebook.css'

], function(QUI, QUIControl, QUILocale, Ajax)
{
    "use strict";

    return new Class({

        Extends : QUIControl,
        Type    : 'package/quiqqer/intranet/bin/social/Facebook',

        options : {
            name   : 'facebook',
            appId  : '',
            styles : false
        },

        initialize : function(options)
        {
            this.parent( options );
        },

        /**
         * Creates the DOMNode Element
         *
         * @return {HTMLElement}
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

            if ( this.getAttribute( 'styles' ) ) {
                this.$Elm.setStyles( this.getAttribute( 'styles' ) );
            }

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

            this.fireEvent( 'loginBegin', [ this ] );

            this.$__PopupCheck = (function()
            {
                self.fireEvent( 'signInError', [ self, false ] );

            }).delay( 4000 );

            this.facebookSignIn(function()
            {
                if ( typeof self.$__PopupCheck !== 'undefined' ) {
                    clearTimeout( self.$__PopupCheck );
                }

                FB.getLoginStatus(function(response)
                {
                    if ( response.status === 'connected' )
                    {
                        FB.api('/me', function(data)
                        {
                            var socialData = {
                                email      : data.email,
                                name       : data.name,
                                gender     : data.gender,
                                lastname   : data.last_name,
                                firstname  : data.first_name,
                                facebookid : data.id,
                                locale     : data.locale,
                                link       : data.link,
                                token      : {
                                    accessToken : response.authResponse.accessToken,
                                    userID      : response.authResponse.userID
                                }
                            };

                            self.fireEvent( 'auth', [ self, socialData ] );
                        });

                        return;
                    }

                    self.fireEvent( 'signInError', [ self, response ] );

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
            if ( typeof QUIQQER_USER !== 'undefined' && parseInt( QUIQQER_USER.id ) ) {
                return;
            }

            this.fireEvent( 'signInBegin', [ this ] );

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
                if ( response.session !== null )
                {
                    self.fireEvent( 'signInError', [ self, response ] );

                    if ( typeof callback !== 'undefined' ) {
                        callback( false );
                    }

                    return;
                }

                self.fireEvent( 'signInEnd', [ self ] );

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
                Ajax.get('package_quiqqer_intranet_ajax_social_clientData', function(clientData)
                {
                    self.setAttribute( 'appId', clientData.facebookAppId );

                    if ( !document.getElementById( 'facebook-jssdk' ) )
                    {
                        var js,
                            fjs = document.getElementsByTagName( 'script' )[0];

                        js     = document.createElement( 'script' );
                        js.id  = 'facebook-jssdk';
                        js.src = "//connect.facebook.net/de_DE/all.js#xfbml=1&appId="+ clientData.facebookAppId;

                        fjs.parentNode.insertBefore( js, fjs );
                    }
                }, {
                    'package' : 'quiqqer/intranet'
                });

            } catch ( e )
            {
                if ( typeOf( callback ) === 'function' ) {
                    callback();
                }
            }
        }
    });
});
