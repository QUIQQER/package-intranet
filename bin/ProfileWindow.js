
/**
 * Profile popup / window
 *
 * @author www.pcsg.de (Henning Leutz)
 * @module package/quiqqer/intranet/bin/ProfileWindow
 */

define([

    'qui/QUI',
    'qui/controls/windows/Popup',
    'package/quiqqer/intranet/bin/Profile'

], function(QUI, QUIPopup, Profile)
{
    "use strict";

    return new Class({

        Extends : QUIPopup,
        Type    : 'package/quiqqer/intranet/bin/ProfileWindow',

        Binds : [
            '$onOpen'
        ],

        options : {
            icon      : 'fa fa-user',
            title     : 'Profil',
            maxWidth  : 1200,
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

            Content.set({
                html   : '',
                styles : {
                    padding : 0
                }
            });

            new Profile({
                header : false
            }).inject( Content );
        }
    });

});
