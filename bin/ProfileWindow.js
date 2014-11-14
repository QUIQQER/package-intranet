
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
            '$onOpen',
            '$onResize'
        ],

        options : {
            icon      : 'fa fa-user',
            title     : 'Profil',
            maxWidth  : 1200,
            maxHeight : 650,
            buttons   : false,
            activeButton : false
        },

        initialize : function(options)
        {
            var self = this;

            this.$resizeDelay = null;
            this.$Profile     = null;

            this.parent( options );

            this.addEvents({
                onOpen   : this.$onOpen,
                onResize : function()
                {
                    if ( self.$resizeDelay ) {
                        clearTimeout( self.$resizeDelay );
                    }

                    self.$resizeDelay = (function() {
                        self.$onResize();
                    }).delay( 200 );
                }
            });
        },

        /**
         * event : onOpen
         */
        $onOpen : function()
        {
            var self    = this,
                Content = this.getContent();

            Content.set({
                html   : '',
                styles : {
                    padding : 0
                }
            });

            this.$Profile = new Profile({
                header       : false,
                activeButton : this.getAttribute( 'activeButton' ),
                events :
                {
                    onProfileDelete : function() {
                        self.close();
                    }
                }
            }).inject( Content );
        },

        /**
         * event : on resize
         */
        $onResize : function()
        {
            this.$Profile.resize();
        }
    });

});
