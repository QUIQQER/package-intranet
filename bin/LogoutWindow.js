
/**
 * Logout popup / window
 *
 * @author www.pcsg.de (Henning Leutz)
 * @module package/quiqqer/intranet/bin/LogoutWindow
 */

define([

    'qui/QUI',
    'qui/controls/windows/Confirm',
    'package/quiqqer/intranet/bin/Login',
    'Locale'

], function(QUI, QUIConfirm, Login, Locale)
{
    "use strict";

    return new Class({

        Extends : QUIConfirm,
        Type    : 'package/quiqqer/intranet/bin/LogoutWindow',

        Binds : [
            'logout',
            '$onOpen'
        ],

        options : {
            icon  : 'icon-sign-out fa fa-sign-out',
            title : Locale.get( 'quiqqer/intranet', 'window.logout.title' ),
            text  : Locale.get( 'quiqqer/intranet', 'window.logout.text' ),
            texticon    : 'icon-sign-out fa fa-sign-out',
            information : Locale.get( 'quiqqer/intranet', 'window.logout.information' ),
            maxWidth    : 500,
            maxHeight   : 300
        },

        initialize : function(options)
        {
            this.parent( options );

            this.addEvents({
                onSubmit : this.logout
            });
        },

        /**
         * Execute the logout
         */
        logout : function()
        {
            window.location = URL_DIR +'?logout=1';
        }
    });

});
