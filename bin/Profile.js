
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
            '$onResize',
            '$onCategoryClick'
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

            this.$ContentControl = null;

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
                name   : 'myData',
                text   : Locale.get( 'quiqqer/intranet', 'profile.btn.mydata' ),
                icon   : 'icon-file-text fa fa-file-text',
                events :
                {
                    onClick : function()  {
                        self.showMyData();
                    }
                }
            }).inject( this.$Buttons );

            this.$buttons.changePassword = new QUIButton({
                name   : 'changePassword',
                text   : Locale.get( 'quiqqer/intranet', 'profile.btn.changepw' ),
                icon   : 'icon-key fa fa-key',
                events :
                {
                    onClick : function()  {
                        self.showChangePassword();
                    }
                }
            }).inject( this.$Buttons );

            this.$buttons.MyAddress = new QUIButton({
                name   : 'address',
                text   : Locale.get( 'quiqqer/intranet', 'profile.btn.my.address' ),
                icon   : 'icon-home fa fa-home',
                events :
                {
                    onClick : function()  {
                        self.showAddress();
                    }
                }
            }).inject( this.$Buttons );


            this.$buttons.Address = new QUIButton({
                name   : 'address',
                text   : Locale.get( 'quiqqer/intranet', 'profile.btn.address' ),
                icon   : 'icon-home fa fa-home',
                events :
                {
                    onClick : function()  {
                        self.showAddresses();
                    }
                }
            }).inject( this.$Buttons );

            // hide all
            this.$buttons.myData.hide();
            this.$buttons.changePassword.hide();
            this.$buttons.MyAddress.hide();
            this.$buttons.Address.hide();


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

            if ( this.$ContentControl ) {
                this.$ContentControl.resize();
            }
        },

        /**
         * event : on inject
         */
        $onInject : function()
        {
            var self = this;

            this.Loader.show();

            Ajax.get([
                'package_quiqqer_intranet_ajax_user_profile_getCategories',
                'package_quiqqer_intranet_ajax_user_profile_config'
            ], function(categories, config)
            {
                var i, len, Btn, Category;

                for ( i = 0, len = categories.length; i < len; i++ )
                {
                    Category = categories[ i ];

                    Btn = new QUIButton({
                        name    : Category.name || '',
                        text    : Category.text || '',
                        icon    : Category.icon || '',
                        require : Category.require || '',
                        events  : {
                            onClick : self.$onCategoryClick
                        }
                    }).inject( self.$Buttons );

                    self.$buttons[ Btn.getId() ] = Btn;
                }

                // show available buttons
                if ( ( config.userProfile.showMyData ).toInt() ) {
                    self.$buttons.myData.show();
                }

                if ( ( config.userProfile.showPasswordChange ).toInt() ) {
                    self.$buttons.changePassword.show();
                }

                if ( ( config.userProfile.showAddress ).toInt() ) {
                    self.$buttons.MyAddress.show();
                }

                if ( ( config.userProfile.showAddressManager ).toInt() ) {
                    self.$buttons.Address.show();
                }


                self.resize();

                self.refresh(function()
                {
                    // fist available button
                    for ( var i in self.$buttons )
                    {
                        if ( self.$buttons[ i ].isHidden() ) {
                            continue;
                        }

                        self.$buttons[ i ].click()
                        break;
                    }
                });
            }, {
                'package' : 'quiqqer/intranet'
            });
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
         * Show the my address
         */
        showAddress : function()
        {
            var self = this;

            this.$normalizeButtons();
            this.$buttons.MyAddress.setActive();

            this.$Content.set( 'html', '' );

            this.Loader.show();

            Ajax.get([
                'package_quiqqer_intranet_ajax_address_getStandard',
                'package_quiqqer_intranet_ajax_address_template'
            ], function(address, template)
            {
                self.$Content.set( 'html', template );

                var Form = self.$Content.getElement( 'form' );

                Form.addClass( 'package-intranet-profile-myaddress' );

                var Header = new Element('h2', {
                    html : Locale.get('quiqqer/intranet', 'profile.myaddress.header')
                }).inject( Form, 'top' );

                new Element('p', {
                    'html' : Locale.get('quiqqer/intranet', 'profile.myaddress.header.description')
                }).inject( Header, 'after' );

                QUIFormUtils.setDataToForm( address, Form );


                new QUIButton({
                    text : Locale.get(
                        'quiqqer/intranet',
                        'address.manager.create.sheet.button.edit'
                    ),
                    textimage : 'icon-save fa fa-save',
                    'class'   : 'btn-green',
                    events    :
                    {
                        onClick : function()
                        {
                            var data = QUIFormUtils.getFormData( Form );

                            self.Loader.show();

                            Ajax.post('package_quiqqer_intranet_ajax_address_edit', function()
                            {
                                self.Loader.hide();
                            }, {
                                'package' : 'quiqqer/intranet',
                                aid       : address.id,
                                data      : JSON.encode( data )
                            });
                        }
                    }
                }).inject( self.$Content );

                self.Loader.hide();

            }, {
                'package' : 'quiqqer/intranet'
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

            this.Loader.show();

            require(['package/quiqqer/intranet/bin/address/Manager'], function(Manager)
            {
                self.$ContentControl = new Manager({
                    events :
                    {
                        onLoad : function() {
                            self.Loader.hide();
                        }
                    }
                }).inject( self.$Content );
            });
        },

        /**
         * set all button to status normal
         */
        $normalizeButtons : function()
        {
            for ( var btn in this.$buttons ) {
                this.$buttons[ btn ].setNormal();
            }

            if ( this.$ContentControl )
            {
                this.$ContentControl.destroy();
                this.$ContentControl = null;
            }
        },

        /**
         * event : category / button click
         *
         * @param {qui/controls/buttons/Button} Btn
         */
        $onCategoryClick : function(Btn)
        {
            var self = this;

            this.$normalizeButtons();
            this.Loader.show();

            Btn.setActive();

            if ( !Btn.getAttribute( 'require' ) )
            {
                if ( this.$ContentControl )
                {
                    this.$ContentControl.destroy();
                    this.$ContentControl = null;
                }

                this.Loader.hide();
                return;
            }

            require([ Btn.getAttribute( 'require' ) ], function(Cls)
            {
                self.$Content.set( 'html', '' );
                self.$ContentControl = new Cls().inject( self.$Content );

                self.Loader.hide();
            });
        }
    });
});
