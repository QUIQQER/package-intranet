/**
 * Login popup / window
 *
 * @author www.pcsg.de (Henning Leutz)
 * @module package/quiqqer/intranet/bin/LoginWindow
 *
 * @require qui/QUI
 * @require qui/controls/windows/Popup
 * @require package/quiqqer/intranet/bin/Login
 */
define('package/quiqqer/intranet/bin/LoginWindow', [

    'qui/QUI',
    'qui/controls/windows/Popup',
    'package/quiqqer/intranet/bin/Login'

], function (QUI, QUIPopup, Login) {
    "use strict";

    return new Class({

        Extends: QUIPopup,
        Type   : 'package/quiqqer/intranet/bin/LoginWindow',

        Binds: [
            '$onOpen'
        ],

        options: {
            icon               : 'icon-signin',
            title              : 'Login',
            maxWidth           : 500,
            maxHeight          : 650,
            buttons            : false,
            registration       : true,
            social             : true,
            passwordReset      : true,
            logo               : false,
            'show-login-failed': true
        },

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                onOpen : this.$onOpen
            });
        },

        /**
         * event : onOpen
         */
        $onOpen: function () {
            var Content = this.getContent();

            Content.set({
                html  : '',
                styles: {
                    padding: 0
                }
            });

            new Login({
                registration       : this.getAttribute('registration'),
                social             : this.getAttribute('social'),
                passwordReset      : this.getAttribute('passwordReset'),
                logo               : this.getAttribute('logo'),
                'show-login-failed': this.getAttribute('show-login-failed'),
                events             : {
                    onLogedIn: function () {
                        var loc = window.location.toString();

                        if (loc.match(/\?logout/g) || loc.match(/logout\=1/g)) {
                            loc = loc.replace('?logout=1', '')
                                .replace('&logout=1', '')
                                .replace('?logout', '');

                            window.location = loc;

                        } else {
                            try {
                                window.location.reload();
                            } catch (e) {
                                window.location = window.location;
                            }
                        }
                    }
                }
            }).inject(Content);
        }
    });
});
