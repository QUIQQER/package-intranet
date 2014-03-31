
/**
 * Profil control
 */

define('quiqqer/intranet/Profile', [

    'qui/QUI',
    'qui/controles/Control',
    'qui/controles/loader/Loader',

    'css!quiqqer/intranet/Profile.css'

], function(QUI, QUIControl, QUILoader)
{
    "use strict";

    return new Class({

        Extends : QUIControl,
        Type    : 'quiqqer/intranet/Profile',

        initialize : function(options)
        {
            this.parent( options );

            this.$Elm   = null;
            this.Loader = new QUILoader();
        },

        /**
         * Return the DOMNode Element
         *
         * @return {DOMNode}
         */
        create : function()
        {
            this.$Elm = new Element('div', {
                'class' : 'package-intranet-profile'
                html    : '<div class="package-intranet-profile-buttons"></div>' +
                          '<div class="package-intranet-profile-content"></div>',

            });

            this.Loader.inject( this.$Elm );

            return this.$Elm;
        }

    });

});