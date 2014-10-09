
/**
 * An User Address
 *
 * @module package/quiqqer/intranet/bin/address/Address
 * @author www.pcsg.de (Henning Leutz)
 */

define([

    'qui/QUI',
    'qui/controls/Control',
    'controls/grid/Grid',
    'Ajax',
    'Locale',

    'css!package/quiqqer/intranet/bin/address/Address.css'

], function(QUI, QUIControl, Grid, Ajax, Locale)
{
    "use strict";


    return new Class({

        Extends : QUIControl,
        Types   : 'package/quiqqer/intranet/bin/address/Address',

        Binds : [
            '$onInject'
        ],

        options : {
            id : false
        },

        initialize : function(options)
        {
            this.parent( options );

            this.addEvents({
                onInject : this.$onInject
            });
        },

        /**
         * Create the DOMNode Element
         *
         * @return {DOMNode}
         */
        create : function()
        {
            this.$Elm = new Element('div', {
                'class' : 'package-intranet-addresses qui-box'
            });

            return this.$Elm;
        },


        $onInject : function()
        {

        }
    });

});
