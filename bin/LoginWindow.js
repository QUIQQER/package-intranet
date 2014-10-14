
/**
 * Login popup / window
 *
 * @author www.pcsg.de (Henning Leutz)
 * @module package/quiqqer/intranet/bin/LoginWindow
 */

define([

    'qui/QUI',
    'qui/controls/windows/Popup',
    'package/quiqqer/intranet/bin/Login'

], function(QUI, QUIPopup, Login)
{
    "use strict";

    return new Class({

        Extends : QUIPopup,
        Type    : 'package/quiqqer/intranet/bin/LoginWindow',

        Binds : [
            '$onOpen'
        ],

        options : {
            icon      : 'icon-signin',
            title     : 'Login',
            maxWidth  : 500,
            maxHeight : 650,
            buttons   : false
        },

        initialize : function(options)
        {
            this.parent( options );

            this.addEvents({
                onOpen : this.$onOpen
            });
        },

        /**
         * event : onOpen
         */
        $onOpen : function()
        {
            var Content = this.getContent();
                Content.set( 'html', '' );

            new Login({
                events :
                {
                    onLogedIn : function()
                    {
                        var loc = window.location.toString();

                        if ( loc.match( /\?logout/g ) || loc.match( /logout\=1/g ) )
                        {
                            loc = loc.replace( '?logout=1', '' )
                                     .replace( '&logout=1', '' )
                                     .replace( '?logout', '' )

                            window.location = loc;

                        } else
                        {
                            window.location = window.location;
                        }
                    }
                }
            }).inject( Content );
        }
    });

});
