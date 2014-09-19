
/**
 * Profil control
 */
//
//define('package/quiqqer/intranet/Profile', [
//
//    'qui/QUI',
//    'qui/controls/Control',
//    'qui/controls/loader/Loader',
//    'qui/controls/buttons/Button',
//    'qui/controls/utils/Background',
//    'Ajax',
//
//    'css!package/quiqqer/intranet/Profile.css'
//
//], function(QUI, QUIControl, QUILoader, QUIButton, QUIBackground, Ajax)
//{
//    "use strict";
//
//    return new Class({
//
//        Extends : QUIControl,
//        Type    : 'quiqqer/intranet/Profile',
//
//        Binds : [
//            '$onInject',
//            '$onResize'
//        ],
//
//        initialize : function(options)
//        {
//            var self = this;
//
//            this.parent( options );
//
//            this.User = false;
//
//            this.$Elm     = null;
//            this.$Menu    = null;
//            this.$Buttons = null;
//            this.$Content = null;
//
//            this.$MenuFX = null;
//
//            this.Loader     = new QUILoader();
//            this.Background = new QUIBackground({
//                styles : {
//                    position : 'absolute',
//                    zIndex   : 9
//                },
//                events : {
//                    onClick : function() {
//                        self.hideMenu();
//                    }
//                }
//            });
//
//            this.$buttons = {};
//
//            this.addEvents({
//                onInject : this.$onInject
//            });
//        },
//
//        /**
//         * Return the DOMNode Element
//         *
//         * @return {DOMNode}
//         */
//        create : function()
//        {
//            var self = this;
//
//            this.$Elm = new Element('div', {
//                'class' : 'package-intranet-profile',
//                html    : '<div class="package-intranet-profile-header box">' +
//                              '<div class="package-intranet-profile-header-menu">' +
//                                  '<span class="icon-reorder"></span>' +
//                              '</div>' +
//                              '<div class="package-intranet-profile-header-text">' +
//                                  '<span class="icon-signin"></span>'+
//                                  '<span>Eingeloggt als:</span>' +
//                                  '<span class="username"></span>' +
//                              '</div>' +
//                          '</div>' +
//                          '<div class="package-intranet-profile-buttons"></div>' +
//                          '<div class="package-intranet-profile-content"></div>'
//            });
//
//            this.Loader.inject( this.$Elm );
//            this.Background.inject( this.$Elm );
//
//            this.$Header  = this.$Elm.getElement( '.package-intranet-profile-header' );
//            this.$Buttons = this.$Elm.getElement( '.package-intranet-profile-buttons' );
//            this.$Content = this.$Elm.getElement( '.package-intranet-profile-content' );
//            this.$Menu    = this.$Elm.getElement( '.package-intranet-profile-header-menu' );
//
//            this.$buttons.myData = new QUIButton({
//                name : 'myData',
//                text : 'Meine Daten',
//                icon : 'icon-file-text',
//                events :
//                {
//                    onClick : function()  {
//                        self.showMyData();
//                    }
//                }
//            }).inject( this.$Buttons );
//
//            this.$buttons.changePassword = new QUIButton({
//                name : 'changePassword',
//                text : 'Passwort ändern',
//                icon : 'icon-key',
//                events :
//                {
//                    onClick : function()  {
//                        self.showChangePassword();
//                    }
//                }
//            }).inject( this.$Buttons );
//
//            this.$Menu.addEvents({
//                click : function() {
//                    self.showMenu();
//                }
//            });
//
//            this.$MenuFX = moofx( this.$Buttons );
//
//
//            this.fireEvent( 'create', [ this ] );
//
//            if ( typeof QUIQQER_USER === 'undefined' ||
//                 !QUIQQER_USER.id ||
//                 QUIQQER_USER.id == '' )
//            {
//                this.showLogin();
//
//            } else
//            {
//                this.$Header.getElement( '.username' ).set( 'html', QUIQQER_USER.name );
//            }
//
//            return this.$Elm;
//        },
//
//        /**
//         * event : on resize
//         */
//        resize : function()
//        {
//            var elmSize    = this.$Elm.getSize(),
//                headerSize = this.$Header.getSize();
//
//            this.$Buttons.setStyles({
//                height : elmSize.y - headerSize.y
//            });
//
//            this.$Content.setStyles({
//                height : elmSize.y - headerSize.y
//            });
//        },
//
//        /**
//         * event : on inject
//         */
//        $onInject : function()
//        {
//            this.Loader.show();
//
//            this.$buttons.myData.click();
//            this.resize();
//        },
//
//        /**
//         * if the User not loged in
//         *
//         * @param {String} username - name of the user
//         * @param {String} password - password
//         * @param {Function} callback - [optional] callback function
//         */
//        login : function(username, password, callback)
//        {
//            Ajax.post('ajax_login_login', function()
//            {
//                if ( typeof callback !== 'undefined' ) {
//                    callback();
//                }
//
//            }, {
//                username : username,
//                password : password
//            });
//        },
//
//        /**
//         * show the login
//         */
//        showLogin : function()
//        {
//            var LoginContainer = new Element('div', {
//                'class' : 'package-intranet-profile-login box',
//                'html'  : '<div class="package-intranet-profile-login-header box">' +
//                              '<span class="icon-signin"></span><span>Login</span>' +
//                          '</div>' +
//                          '<form class="package-intranet-profile-login-content box">'+
//                              '<h1>Login</h1>' +
//                              '<input type="text" value="" name="username" placeholder="Username / e-mail">' +
//                              '<input type="password" value="" name="password" placeholder="Password">' +
//                              '<input class="login button btn-green" type="submit" value="Login!">' +
//                          '</form>'
//            }).inject( this.$Elm );
//
//            var self     = this,
//                Form     = LoginContainer.getElement( '.package-intranet-profile-login-content' ),
//                Username = LoginContainer.getElement( '[name="username"]' ),
//                Password = LoginContainer.getElement( '[name="password"]' );
//
//            Form.addEvents({
//                submit : function(event)
//                {
//                    if ( event ) {
//                        event.stop();
//                    }
//
//                    self.Loader.show();
//
//                    self.login( Username.value, Password.value, function()
//                    {
//                        LoginContainer.destroy();
//
//                        self.Loader.hide();
//                    });
//                }
//            });
//        },
//
//        /**
//         * Shows profile menu
//         */
//        showMenu : function()
//        {
//            this.Background.show();
//
//            this.$MenuFX.animate({
//                left : 0
//            });
//        },
//
//        /**
//         * Shows profile menu
//         */
//        hideMenu : function()
//        {
//            var self = this;
//
//            this.$MenuFX.animate({
//                left : '-100%'
//            }, {
//                callback : function() {
//                    self.Background.hide();
//                }
//            });
//        },
//
//        /**
//         * Show the data
//         */
//        showMyData : function()
//        {
//            var self = this;
//
//            this.$normalizeButtons();
//            this.$buttons.myData.setActive();
//
//            Ajax.get('package_quiqqer_intranet_ajax_user_profil_data', function(result)
//            {
//                self.$Content.set( 'html', result );
//
//                new QUIButton({
//                    text : Locale.get( 'quiqqer/intranet', 'save' ),
//                    icon : 'icon-save'
//                }).inject( self.$Content );
//
//                self.hideMenu();
//                self.Loader.hide();
//            }, {
//                'package' : 'quiqqer/intranet'
//            });
//        },
//
//        /**
//         * Show the change password
//         */
//        showChangePassword : function()
//        {
//            var self = this;
//
//            this.$normalizeButtons();
//            this.$buttons.changePassword.setActive();
//
//            Ajax.get('package_quiqqer_intranet_ajax_user_profil_password', function(result)
//            {
//                self.$Content.set( 'html', result );
//
//                self.hideMenu();
//                self.Loader.hide();
//            }, {
//                'package' : 'quiqqer/intranet'
//            });
//
//            this.Loader.hide();
//        },
//
//        /**
//         * set all button to status normal
//         */
//        $normalizeButtons : function()
//        {
//            for ( var btn in this.$buttons ) {
//                this.$buttons[ btn ].setNormal();
//            }
//        }
//    });
//
//});