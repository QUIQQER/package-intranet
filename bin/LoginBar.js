
/**
 * Login popup / window
 *
 * @author www.pcsg.de (Henning Leutz)
 * @module package/quiqqer/intranet/Login
 */

define([

    'qui/QUI',
    'qui/controls/windows/Popup',
    'qui/controls/buttons/Button',
    'qui/Locale',
    'package/quiqqer/intranet/LoginWindow',
    'package/quiqqer/intranet/Registration',

    'css!package/quiqqer/intranet/LoginBar.css'

], function(QUI, QUIPopup, QUIButton, QUILocale, LoginWin, Registration)
{
    "use strict";

    return new Class({

        Extends : QUIPopup,
        Type    : 'package/quiqqer/intranet/LoginBar',

        Binds : [
            '$onInject'
        ],

        options : {
            styles : false
        },

        initialize : function(options)
        {
            this.parent( options );
            this.Registration = new Registration();

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
            this.$Elm = new Element('div', {
                'class' : 'quiqqer-intranet-login-bar'
            });

            if ( this.getAttribute( 'styles' ) ) {
                this.$Elm.setStyles( this.getAttribute( 'styles' ) );
            }

            return this.$Elm;
        },

        /**
         * refresh the control
         */
        refresh : function()
        {
            // user ist angemeldet
            if ( this.Registration.isLogedIn() )
            {
                this.$Elm.set(
                    'html',

                    '<div class="quiqqer-intranet-login-bar-text">' +
                        QUILocale.get( 'quiqqer/intranet', 'loged.in.as', {
                            username : QUIQQER_USER.name
                        }) +
                    '</div>'
                );

                new QUIButton({
                    'class' : 'icon-signout',
                    events  :
                    {
                        onClick : function() {
                            window.location = '?logout';
                        }
                    }
                }).inject( this.$Elm );

                return;
            }

            new QUIButton({
                textimage : 'icon-signin',
                text   : 'Login',
                events :
                {
                    onClick : function() {
                        new LoginWin().open();
                    }
                }
            }).inject( this.$Elm );
        },

        /**
         * event : on inject
         */
        $onInject : function()
        {
            this.refresh();
        }
    });

});
