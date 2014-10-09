
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
    'qui/controls/buttons/Button',
    'qui/controls/loader/Loader',
    'qui/utils/Form',
    'controls/grid/Grid',
    'Ajax',
    'Locale',

    'css!package/quiqqer/intranet/bin/address/Manager.css'

], function(QUI, QUIControl, QUIButton, QUILoader, QUIFormUtils, Grid, Ajax, Locale)
{
    "use strict";


    return new Class({

        Extends : QUIControl,
        Types   : 'package/quiqqer/intranet/bin/address/Manager',

        Binds : [
            'openCreateAddress',
            '$onInject'
        ],

        initialize : function(options)
        {
            this.parent( options );

            this.$Grid = null;

            this.Loader = new QUILoader();

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

            this.Loader.inject( this.$Elm );

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
                    textimage : 'fa fa-plus icon-plus',
                    events    : {
                        onClick : this.openCreateAddress
                    }
                }, {
                    text      : 'Markierte Adresse bearbeiten',
                    textimage : 'fa fa-edit icon-edit',
                    disabled  : true,
                    events    :
                    {
                        onClick : function() {
                            self.openCreateAddress( self.$Grid.getSelectedData()[ 0 ].id );
                        }
                    }
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

            this.$Grid.addEvents({
                onClick : function()
                {

                },

                onDblClick : function() {
                    self.openCreateAddress( self.$Grid.getSelectedData()[ 0 ].id );
                }
            });
        },

        /**
         * refresh the address list
         */
        refresh : function(callback)
        {
            var self = this;

            this.Loader.show();

            Ajax.get('package_quiqqer_intranet_ajax_address_list', function(result)
            {
                self.$Grid.setData({
                    data : result
                });

                if ( typeof callback !== 'undefined' ) {
                    callback();
                }

                self.Loader.hide();
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

            this.$Grid.resize();
        },

        /**
         * Return the address data from a specific address ID
         *
         * @param {Integer} aid - Adress-ID
         * @param {Function} callback - callback function
         */
        getAddress : function(aid, callback)
        {
            Ajax.get('package_quiqqer_intranet_ajax_address_get', function(data)
            {
                callback( data );
            }, {
                'package' : 'quiqqer/intranet',
                aid       : aid
            });
        },

        /**
         * Opens the create address panel
         *
         * @param {Integer} aid - [optional] Adress ID, if no ID, a new address would be created
         */
        openCreateAddress : function(aid)
        {
            var self = this;

            self.Loader.show();

            this.openSheet(function(Content, Sheet)
            {
                var Buttons = Sheet.getElement( '.qui-sheet-buttons' );

                Ajax.get('package_quiqqer_intranet_ajax_address_template', function(result)
                {
                    Content.set( 'html', result );

                    var edit = false,
                        Form = Content.getElement( 'form' );

                    if ( typeof aid !== 'undefined' ) {
                        edit = true;
                    }

                    Buttons.set( 'html', '' );
                    Buttons.setStyles({
                        textAlign : 'right'
                    });

                    new QUIButton({
                        text      : 'Abbrechen',
                        textimage : 'fa fa-close',
                        styles    : {
                            margin : '0 10px 0'
                        },
                        events :
                        {
                            onClick : function() {
                                Sheet.fireEvent( 'close' );
                            }
                        }
                    }).inject( Buttons );

                    new QUIButton({
                        text      : edit ? 'Adresse speichern' : 'Neue Adresse anlegen',
                        textimage : 'icon-save fa fa-save',
                        'class'   : 'btn-green',
                        events    :
                        {
                            onClick : function()
                            {
                                var data = QUIFormUtils.getFormData( Form );

                                if ( edit )
                                {
                                    self.editAddress( aid, data, function()
                                    {
                                        Sheet.fireEvent( 'close' );
                                        self.refresh();
                                    });

                                    return;
                                }

                                self.execCreateAddress( data, function()
                                {
                                    Sheet.fireEvent( 'close' );
                                    self.refresh();
                                } );
                            }
                        }
                    }).inject( Buttons );

                    if ( edit === false )
                    {
                        self.Loader.hide();
                        return;
                    }

                    // load address
                    self.getAddress(aid, function(data)
                    {
                        QUIFormUtils.setDataToForm( data, Form );

                        self.Loader.hide();
                    });

                }, {
                    'package' : 'quiqqer/intranet'
                });
            });
        },

        /**
         * Create a new address
         *
         * @param {Object} data - Address data
         * @param {Function} callback - [optional] callback function
         */
        createAddress : function(data, callback)
        {
            Ajax.post('package_quiqqer_intranet_ajax_address_create', function()
            {
                if ( typeof callback !== 'undefined' ) {
                    callback();
                }
            }, {
                'package' : 'quiqqer/intranet',
                data      : JSON.encode( data )
            });
        },

        /**
         * Edit an address
         *
         * @param {Integer} aid - Address ID
         * @param {Object} data - Address data
         * @param {Function} callback - [optional] callback function
         */
        editAddress : function(aid, data, callback)
        {
            Ajax.post('package_quiqqer_intranet_ajax_address_edit', function()
            {
                if ( typeof callback !== 'undefined' ) {
                    callback();
                }
            }, {
                'package' : 'quiqqer/intranet',
                aid       : aid,
                data      : JSON.encode( data )
            });
        }
    });
});
