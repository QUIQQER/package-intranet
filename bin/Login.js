/**
 * Login control
 *
 * @module package/quiqqer/intranet/bin/Login
 * @author www.pcsg.de (Henning Leutz)
 *
 * @event onLoginBegin [ {self} ]
 * @event onLogedIn [ {self}, {object} user data ]
 *
 * @require qui/QUI
 * @require qui/controls/Control
 * @require qui/controls/loader/Loader
 * @require qui/controls/buttons/Button
 * @require qui/controls/desktop/panels/Sheet
 * @require Ajax
 * @require Locale
 * @require package/quiqqer/intranet/bin/Registration
 * @require css!package/quiqqer/intranet/bin/Login.css
 */
define('package/quiqqer/intranet/bin/Login', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/loader/Loader',
    'qui/controls/buttons/Button',
    'qui/controls/desktop/panels/Sheet',
    'Ajax',
    'Locale',
    'package/quiqqer/intranet/bin/Registration',
    URL_OPT_DIR + 'bin/mustache/mustache.min.js',

    'text!package/quiqqer/intranet/bin/Login.html',
    'css!package/quiqqer/intranet/bin/Login.css'

], function (QUI, QUIControl, QUILoader, QUIButton, QUISheet, Ajax, QUILocale,
             Registration, Mustache, template) {
    "use strict";

    var lg = 'quiqqer/intranet';

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/intranet/bin/Login',

        Binds: [
            '$onInject',
            '$onAuth'
        ],

        options: {
            registration : true,
            social       : true,
            passwordReset: true,
            logo         : false
        },

        initialize: function (options) {
            this.parent(options);

            this.Loader       = null;
            this.Registration = new Registration();

            this.$Username = null;
            this.$Password = null;

            this.addEvents({
                onInject: this.$onInject
            });
        },

        /**
         * refresh control
         */
        refresh: function () {
            // user ist angemeldet
            if (this.Registration.isLogedIn()) {
                this.$Elm.set(
                    'html',

                    '<p>' +
                    QUILocale.get(lg, 'loged.in.as', {
                        username: QUIQQER_USER.name
                    }) +
                    ' <a href="?logout" class="icon-signout"></a>' +
                    '</p>'
                );

                return;
            }

            var self   = this,
                action = '',
                logo   = '';

            if ('httpshost' in QUIQQER_PROJECT && QUIQQER_PROJECT.httpshost) {
                action = QUIQQER_PROJECT.httpshost;
                action = action + window.location.pathname;
                action = action + '?' + window.location.search;
            }

            if (this.getAttribute('logo')) {
                logo = '<img src="' + this.getAttribute('logo') + '" class="quiqqer-intranet-login-logo" />';
            }

            this.$Elm.set({
                html: Mustache.render(template, {
                    logo         : logo,
                    action       : action,
                    title        : QUILocale.get(lg, 'login.in.title'),
                    passwordLink : QUILocale.get(lg, 'login.in.forgotten.password.link'),
                    orText       : QUILocale.get(lg, 'login.in.or.text'),
                    titleSocial  : QUILocale.get(lg, 'login.in.sign.in.title'),
                    titleRegister: QUILocale.get(lg, 'login.in.register.text'),
                    registerLink : QUILocale.get(lg, 'login.in.register.link')
                })
            });

            this.$Username = this.$Elm.getElement('[name="username"]');
            this.$Password = this.$Elm.getElement('[name="password"]');

            this.$Elm.getElement('.quiqqer-intranet-login-forget-link')
                .addEvent('click', function () {
                    self.showForgetPassword();
                });


            this.$Elm.getElement('.quiqqer-intranet-login-reg-content-registration-link')
                .addEvent('click', function () {
                    self.openRegistration();
                });

            this.$Username.placeholder = QUILocale.get(lg, 'login.in.username.placeholder');
            this.$Password.placeholder = QUILocale.get(lg, 'login.in.password.placeholder');


            if (this.getAttribute('registration') === false) {
                this.$Elm.getElement(
                    '.quiqqer-intranet-login-registration'
                ).setStyle('display', 'none');
            }

            if (this.getAttribute('social') === false) {
                this.$Elm.getElement(
                    '.quiqqer-intranet-login-social'
                ).setStyle('display', 'none');
            }

            if (this.getAttribute('passwordReset') === false) {
                this.$Elm.getElement(
                    '.quiqqer-intranet-login-forget-link'
                ).setStyle('display', 'none');
            }
        },

        /**
         * Create the DOMNode Element
         *
         * @return {HTMLElement}
         */
        create: function () {
            this.$Elm = this.parent();
            this.$Elm.addClass('quiqqer-intranet-login');

            this.refresh();

            this.Loader = new QUILoader();
            this.Loader.inject(this.$Elm);

            return this.$Elm;
        },

        /**
         * load social media buttons
         */
        $onInject: function () {
            this.Loader.show();

            // social login
            var self   = this,
                Social = this.$Elm.getElement('.quiqqer-intranet-login-social');

            if (!Social) {
                this.Loader.hide();
                return;
            }

            require([
                'package/quiqqer/intranet/bin/social/Google',
                'package/quiqqer/intranet/bin/social/Facebook'
            ], function (Google, Facebook) {
                new Google({
                    styles    : {
                        display: 'inline-block',
                        'float': 'none'
                    },
                    shwoErrors: false,
                    events    : {
                        onLoginBegin : function () {
                            self.Loader.show();
                            self.fireEvent('loginBegin', [self]);
                        },
                        onAuth       : self.$onAuth,
                        onSignInError: function () {
                            QUI.getMessageHandler(function (MH) {
                                MH.addError(
                                    QUILocale.get(
                                        lg,
                                        'exception.registration.error'
                                    )
                                );
                            });

                            self.Loader.hide();
                        },
                        onSignInEnd  : function () {
                            self.Loader.hide();
                        }
                    }
                }).inject(Social);

                new Facebook({
                    styles    : {
                        display: 'inline-block',
                        'float': 'none'
                    },
                    showErrors: false,
                    events    : {
                        onLoginBegin : function () {
                            self.Loader.show();
                            self.fireEvent('loginBegin', [self]);
                        },
                        onAuth       : self.$onAuth,
                        onSignInError: function () {
                            QUI.getMessageHandler(function (MH) {
                                MH.addError(
                                    QUILocale.get(
                                        lg,
                                        'exception.registration.error'
                                    )
                                );
                            });

                            self.Loader.hide();
                        },
                        onSignInEnd  : function () {
                            self.Loader.hide();
                        }
                    }
                }).inject(Social);

                self.Loader.hide();
            });
        },

        /**
         * social login -> event onAuth
         *
         * @param {Object} Social - package/quiqqer/intranet/social/Google | package/quiqqer/intranet/social/Facebook
         * @param {Object} params - Social params
         */
        $onAuth: function (Social, params) {
            var self = this;

            Ajax.post('package_quiqqer_intranet_ajax_user_socialLogin', function (result) {
                if (!result) {
                    self.Loader.hide();
                    return;
                }

                window.QUIQQER_USER = {
                    id  : result.id,
                    name: result.username,
                    lang: result.lang
                };

                self.refresh();
                self.fireEvent('logedIn', [self, result]);

            }, {
                token     : JSON.encode(params.token),
                socialType: Social.getAttribute('name'),
                project   : JSON.encode({
                    name: QUIQQER_PROJECT.name,
                    lang: QUIQQER_PROJECT.lang
                }),
                'package' : lg,
                showError : false,
                onError   : function (Exception) {
                    if (Exception.getCode() == 404) {
                        self.openRegistration();
                        return;
                    }

                    self.Loader.hide();

                    QUI.getMessageHandler(function (MH) {
                        MH.addError(Exception.getMessage());
                    });
                }
            });
        },

        /**
         * open the registration -> makes a redirect to the registration
         */
        openRegistration: function () {
            var self = this;

            this.Loader.show();

            Ajax.get('package_quiqqer_intranet_ajax_user_getRegisterLink', function (result) {
                self.Loader.show();

                if (window.location.toString() != result) {
                    window.location = result;
                }

            }, {
                project  : JSON.encode({
                    name: QUIQQER_PROJECT.name,
                    lang: QUIQQER_PROJECT.lang
                }),
                'package': lg
            });
        },

        /**
         * forget password methods
         */

        /**
         * Show the forget password sheet
         */
        showForgetPassword: function () {
            var self = this;

            new QUISheet({
                header : false,
                buttons: false,
                styles : {
                    background: '#FFFFFF'
                },
                events : {
                    onOpen: function (Sheet) {
                        var Content = Sheet.getContent();

                        Content.set(
                            'html',

                            '<form class="quiqqer-intranet-login-forget" action="">' +
                            '<h1>' + QUILocale.get(lg, 'pass.forget.h1.text') + '</h1>' +

                            '<label for="quiqqer-intranet-login-forget-email">' +
                            QUILocale.get(lg, 'pass.forget.email.label') +
                            '</label>' +
                            '<input id="quiqqer-intranet-login-forget-email" type="text" value="" required="required" />' +

                            '<div class="quiqqer-intranet-login-forget-buttons">' +
                            '<div class="cancel qui-button btn-white">' +
                            '<span>' + QUILocale.get(lg, 'pass.forget.btn.cancel') + '</span>' +
                            '</div>' +
                            '<div class="quiqqer-intranet-login-forget-sendPW qui-button btn-green">' +
                            '<span>' + QUILocale.get(lg, 'btn.forget.pw.send.email') + '</span>' +
                            '</div>' +
                            '</div>' +
                            '</form>'
                        );

                        Content.getElement('.cancel').addEvent(
                            'click',
                            function () {
                                Sheet.hide();
                            }
                        );

                        Content.getElement('.quiqqer-intranet-login-forget-sendPW').addEvent(
                            'click',
                            function () {
                                Content.getElement('form').fireEvent('submit');
                            }
                        );

                        Content.getElement('form').addEvent(
                            'submit',
                            function (event) {
                                if (typeof event !== 'undefined') {
                                    event.stop();
                                }

                                self.Loader.show();

                                self.sendForgetPassword(
                                    Content.getElement('input').value,
                                    function () {
                                        self.Loader.hide();
                                        Sheet.hide();
                                    }
                                );
                            }
                        );

                        document.id('quiqqer-intranet-login-forget-email').focus();
                    },

                    onClose: function (Sheet) {
                        Sheet.destroy();
                    }
                }
            }).inject(this.$Elm).show();
        },

        /**
         * Send a password forgotten mail to the user
         *
         * @param {String} user - E-Mail, Username, User-Id
         * @param {Function} callback - callback function
         */
        sendForgetPassword: function (user, callback) {
            if (user === '') {
                callback();
                return;
            }

            Ajax.post('package_quiqqer_intranet_ajax_user_password_forgotten', function () {
                if (typeof callback !== 'undefined') {
                    callback();
                }
            }, {
                user     : user,
                project  : JSON.encode({
                    name: QUIQQER_PROJECT.name,
                    lang: QUIQQER_PROJECT.lang
                }),
                'package': lg
            });
        }
    });
});
