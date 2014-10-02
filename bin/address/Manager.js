
/**
 * Address manager control
 * The user can manage his addresses
 *
 * @module package/quiqqer/intranet/bin/address/Manager
 * @author www.pcsg.de (Henning Leutz)
 *
 * @onLoad [ {self} ]
 */

define([

    'qui/QUI',
    'qui/controls/Control',
    'controls/grid/Grid',
    'Ajax',
    'Locale'

], function(QUI, QUIControl, Grid, Ajax, Locale)
{
    "use strict";


    return new Class({

        Extends : QUIControl,
        Types   : 'package/quiqqer/intranet/bin/address/Manager',

        Binds : [
            '$onInject'
        ],

        initialize : function(options)
        {
            this.parent( options );

            this.$Grid = null;

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

        /**
         * event : on inject
         */
        $onInject : function()
        {
            var self = this,
                size = this.$Elm.getSize(),

                GridContainer = new Element( 'div' ).inject( this.$Elm );

            var lg = 'quiqqer/system';

            this.$Grid = new Grid(GridContainer, {
                columnModel : [{
                    header    : Locale.get( lg, 'id' ),
                    dataIndex : 'id',
                    dataType  : 'string',
                    hidden    : true
                }, {
                    header    : Locale.get( lg, 'salutation' ),
                    dataIndex : 'salutation',
                    dataType  : 'string',
                    width     : 60
                }, {
                    header    : Locale.get( lg, 'firstname' ),
                    dataIndex : 'firstname',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( lg, 'lastname' ),
                    dataIndex : 'lastname',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( lg, 'users.user.address.table.phone' ),
                    dataIndex : 'phone',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( lg, 'email' ),
                    dataIndex : 'mail',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( lg, 'company' ),
                    dataIndex : 'company',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( lg, 'street' ),
                    dataIndex : 'street_no',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( lg, 'zip' ),
                    dataIndex : 'zip',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( lg, 'city' ),
                    dataIndex : 'city',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( lg, 'country' ),
                    dataIndex : 'country',
                    dataType  : 'string',
                    width     : 100
                }],
                buttons : [{
                    text      : 'Adresse hinzufügen',
                    textimage : 'fa fa-plus icon-plus'
                }, {
                    text      : 'Markierte Adresse bearbeiten',
                    textimage : 'fa fa-edit icon-edit',
                    disabled  : true
                }, {
                    text      : 'Markierte Adressen löschen',
                    textimage : 'fa fa-trash icon-trash',
                    disabled  : true
                }],
                height : size.y
            });

            this.refresh(function() {
                self.fireEvent( 'load', [ self ] );
            });
        },

        /**
         * refresh the address list
         */
        refresh : function(callback)
        {
            var self = this;

            Ajax.get('package_quiqqer_intranet_ajax_address_list', function(result)
            {
                self.$Grid.setData({
                    data : result
                });

                if ( typeof callback !== 'undefined' ) {
                    callback();
                }
            }, {
                'package' : 'quiqqer/intranet'
            });
        },

        /**
         * Resize the control
         */
        resize : function()
        {
            if ( !this.$Grid ) {
                return;
            }

            console.log( 'resize' );
        }

    });
});