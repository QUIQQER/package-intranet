
/**
 * Profil control
 *
 * @author www.pcsg.de (Henning Leutz)
 */

define([

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/loader/Loader',
    'qui/controls/buttons/Button',
    'qui/controls/utils/Background',
    'qui/utils/Form',
    'Ajax',
    'Locale',

    'css!package/quiqqer/intranet/bin/Profile.css'

], function()
{
    "use strict";

    var QUI           = arguments[ 0 ],
        QUIControl    = arguments[ 1 ],
        QUILoader     = arguments[ 2 ],
        QUIButton     = arguments[ 3 ],
        QUIBackground = arguments[ 4 ],
        QUIFormUtils  = arguments[ 5 ],
        Ajax          = arguments[ 6 ],
        Locale        = arguments[ 7 ];


    return new Class({

        Extends : QUIControl,
        Type    : 'package/quiqqer/intranet/bin/Profile',

        Binds : [
            '$onInject',
            '$onResize'
        ],

        options : {
            header : true // show the header
        },

        initialize : function(options)
        {
            var self = this;

            this.parent( options );

            this.User = false;

            this.$Elm     = null;
            this.$Menu    = null;
            this.$Buttons = null;
            this.$Content = null;

            this.$MenuFX = null;

            this.Loader     = new QUILoader();
            this.Background = new QUIBackground({
                styles : {
                    position : 'absolute',
                    zIndex   : 9
                },
                events : {
                    onClick : function() {
                        self.hideMenu();
                    }
                }
            });

            this.$buttons = {};
            this.$data    = {};

            this.addEvents({
                onInject : this.$onInject
            });
        },

        /**
         * Return the DOMNode Element
         *
         * @return {DOMNode}
         */
        create : function()
        {
            var self = this;

            this.$Elm = new Element('div', {
                'class' : 'package-intranet-profile qui-box',
                html    : '<div class="package-intranet-profile-header box">' +
                              '<div class="package-intranet-profile-header-menu">' +
                                  '<span class="icon-reorder"></span>' +
                              '</div>' +
                              '<div class="package-intranet-profile-header-text">' +
                                  '<span class="icon-signin"></span>'+
                                  '<span class="title"></span>' +
                              '</div>' +
                          '</div>' +
                          '<div class="package-intranet-profile-buttons"></div>' +
                          '<div class="package-intranet-profile-content"></div>'
            });

            this.Loader.inject( this.$Elm );
            this.Background.inject( this.$Elm );

            this.$Header  = this.$Elm.getElement( '.package-intranet-profile-header' );
            this.$Buttons = this.$Elm.getElement( '.package-intranet-profile-buttons' );
            this.$Content = this.$Elm.getElement( '.package-intranet-profile-content' );
            this.$Menu    = this.$Elm.getElement( '.package-intranet-profile-header-menu' );

            this.$buttons.myData = new QUIButton({
                name : 'myData',
                text : Locale.get( 'quiqqer/intranet', 'profile.btn.mydata' ),
                icon : 'icon-file-text',
                events :
                {
                    onClick : function()  {
                        self.showMyData();
                    }
                }
            }).inject( this.$Buttons );

            this.$buttons.changePassword = new QUIButton({
                name : 'changePassword',
                text : Locale.get( 'quiqqer/intranet', 'profile.btn.changepw' ),
                icon : 'icon-key',
                events :
                {
                    onClick : function()  {
                        self.showChangePassword();
                    }
                }
            }).inject( this.$Buttons );


            this.$buttons.Address = new QUIButton({
                name : 'address',
                text : Locale.get( 'quiqqer/intranet', 'profile.btn.address' ),
                icon : 'icon-home',
                events :
                {
                    onClick : function()  {
                        self.showAddresses();
                    }
                }
            }).inject( this.$Buttons );



            this.$Menu.addEvents({
                click : function() {
                    self.showMenu();
                }
            });

            this.$MenuFX = moofx( this.$Buttons );


            this.fireEvent( 'create', [ this ] );

            if ( typeof QUIQQER_USER === 'undefined' ||
                 !QUIQQER_USER.id ||
                 QUIQQER_USER.id == '' )
            {
                this.showLogin();

            } else
            {
                this.$Header.getElement( '.title' ).set( 'html', Locale.get(
                    'quiqqer/intranet',
                    'profile.control.title',
                    {
                        username : QUIQQER_USER.name
                    }
                ));
            }

            if ( this.getAttribute( 'header' ) === false ) {
                this.$Header.setStyle( 'display', 'none' );
            }

            return this.$Elm;
        },

        /**
         * refresh the user data
         */
        refresh : function(callback)
        {
            if ( typeof QUIQQER_USER === 'undefined' ||
                 !QUIQQER_USER.id ||
                 QUIQQER_USER.id == '' )
            {
                callback();
                return;
            }

            if ( this.Loader ) {
                this.Loader.show();
            }

            var self = this;

            Ajax.get('package_quiqqer_intranet_ajax_user_data', function(result)
            {
                self.$data = result;

                self.$Header.getElement( '.title' ).set( 'html', Locale.get(
                    'quiqqer/intranet',
                    'profile.control.title',
                    {
                        username : QUIQQER_USER.name
                    }
                ));

                if ( typeof callback !== 'undefined' ) {
                    callback();
                }

            }, {
                'package' : 'quiqqer/intranet'
            });
        },

        /**
         * event : on resize
         */
        resize : function()
        {
            var elmSize    = this.$Elm.getSize(),
                headerSize = this.$Header.getSize();

            this.$Buttons.setStyles({
                height : elmSize.y - headerSize.y
            });

            this.$Content.setStyles({
                height : elmSize.y - headerSize.y
            });
        },

        /**
         * event : on inject
         */
        $onInject : function()
        {
            this.Loader.show();
            this.resize();

            this.refresh(function() {
                this.$buttons.myData.click();
            }.bind( this ));
        },

        /**
         * if the User not loged in
         *
         * @param {String} username - name of the user
         * @param {String} password - password
         * @param {Function} callback - [optional] callback function
         */
        login : function(username, password, callback)
        {
            var self = this;

            Ajax.post('ajax_login_login', function(data)
            {
                window.QUIQQER_USER = {
                    id   : data.id,
                    name : data.username,
                    lang : data.lang
                };

                self.refresh(function()
                {
                    if ( typeof callback !== 'undefined' ) {
                        callback();
                    }
                });

            }, {
                username : username,
                password : password
            });
        },

        /**
         * show the login
         */
        showLogin : function()
        {
            var LoginContainer = new Element('div', {
                'class' : 'package-intranet-profile-login box',
                'html'  : '<div class="package-intranet-profile-login-header box">' +
                              '<span class="icon-signin"></span><span>Login</span>' +
                          '</div>' +
                          '<form class="package-intranet-profile-login-content box">'+
                              '<h1>Login</h1>' +
                              '<input type="text" value="" name="username" placeholder="Username / e-mail">' +
                              '<input type="password" value="" name="password" placeholder="Password">' +
                              '<input class="login button btn-green" type="submit" value="Login!">' +
                          '</form>'
            }).inject( this.$Elm );

            var self     = this,
                Form     = LoginContainer.getElement( '.package-intranet-profile-login-content' ),
                Username = LoginContainer.getElement( '[name="username"]' ),
                Password = LoginContainer.getElement( '[name="password"]' );

            Form.addEvents({
                submit : function(event)
                {
                    if ( event ) {
                        event.stop();
                    }

                    self.Loader.show();

                    self.login( Username.value, Password.value, function()
                    {
                        LoginContainer.destroy();

                        self.Loader.hide();
                    });
                }
            });
        },

        /**
         * Shows profile menu
         */
        showMenu : function()
        {
            this.Background.show();

            this.$MenuFX.animate({
                left : 0
            });
        },

        /**
         * Shows profile menu
         */
        hideMenu : function()
        {
            var self = this;

            this.$MenuFX.animate({
                left : '-100%'
            }, {
                callback : function() {
                    self.Background.hide();
                }
            });
        },

        /**
         * Show the data
         */
        showMyData : function()
        {
            var self = this;

            this.$normalizeButtons();
            this.$buttons.myData.setActive();

            Ajax.get('package_quiqqer_intranet_ajax_user_profile_data', function(result)
            {
                self.$Content.set( 'html', result );

                new QUIButton({
                    text      : Locale.get( 'quiqqer/system', 'save' ),
                    textimage : 'icon-save',
                    styles    : {
                        margin : '0 0 20px'
                    },
                    events :
                    {
                        onClick : function() {
                            self.saveData();
                        }
                    }
                }).inject( self.$Content );


                var Form = self.$Content.getElement( 'form' );

                Form.addEvent('submit', function(event) {
                    event.stop();
                });

                QUIFormUtils.setDataToForm( self.$data, Form );


                self.hideMenu();
                self.Loader.hide();
            }, {
                'package' : 'quiqqer/intranet',
                lang      : Locale.getCurrent()
            });
        },

        /**
         * Save the data
         */
        saveData : function()
        {
            var Form = this.$Content.getElement( 'form[name="mydata"]' );

            if ( !Form ) {
                return;
            }

            this.Loader.show();

            var self     = this,
                formData = QUIFormUtils.getFormData( Form );

            var data = {
                firstname : formData.firstname,
                lastname  : formData.lastname,
                birthday  : formData.birth_year +'-'+ formData.birth_month +'-'+ formData.birth_day,
                email     : formData.email
            };

            Ajax.post('package_quiqqer_intranet_ajax_user_save', function()
            {
                self.refresh(function() {
                    self.$buttons.myData.click();
                });
            }, {
                'package' : 'quiqqer/intranet',
                data      : JSON.encode( data ),
                lang      : Locale.getCurrent()
            });
        },

        /**
         * Show the change password
         */
        showChangePassword : function()
        {
            var self = this;

            this.$normalizeButtons();
            this.$buttons.changePassword.setActive();

            Ajax.get('package_quiqqer_intranet_ajax_user_profile_password', function(result)
            {
                self.$Content.set( 'html', result );

                self.$Content.getElements( 'form' ).addEvent('submit', function(event) {
                    event.stop();
                });

                new QUIButton({
                    text      : Locale.get( 'quiqqer/system', 'save' ),
                    textimage : 'icon-save',
                    styles    : {
                        margin : '0 0 20px'
                    },
                    events :
                    {
                        onClick : function() {
                            self.changePassword();
                        }
                    }
                }).inject( self.$Content );

                self.hideMenu();
                self.Loader.hide();
            }, {
                'package' : 'quiqqer/intranet'
            });

            this.Loader.hide();
        },

        /**
         * change the password
         */
        changePassword : function()
        {
            var Form = this.$Content.getElement( 'form[name="changepw"]' );

            if ( !Form ) {
                return;
            }

            this.Loader.show();

            var self     = this,
                formData = QUIFormUtils.getFormData( Form );

            var data = {
                firstname : formData.firstname,
                lastname  : formData.lastname,
                birthday  : formData.birth_year +'-'+ formData.birth_month +'-'+ formData.birth_day,
                email     : formData.email
            };

            Ajax.post('package_quiqqer_intranet_ajax_user_password_change', function()
            {
                self.refresh(function() {
                    self.$buttons.changePassword.click();
                });
            }, {
                'package' : 'quiqqer/intranet',
                data      : JSON.encode( data ),
                lang      : Locale.getCurrent()
            });
        },

        /**
         * show the address managegement
         */
        showAddresses : function()
        {
            var self = this;

            this.$normalizeButtons();
            this.$buttons.Address.setActive();

            this.$Content.set( 'html', '' );
        },


        editAdress : function(aid)
        {

        },


        /**
         * set all button to status normal
         */
        $normalizeButtons : function()
        {
            for ( var btn in this.$buttons ) {
                this.$buttons[ btn ].setNormal();
            }
        }
    });

});