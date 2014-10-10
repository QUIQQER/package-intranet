
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
    'qui/controls/windows/Confirm',
    'qui/utils/Form',
    'controls/grid/Grid',
    'Ajax',
    'Locale',

    'css!package/quiqqer/intranet/bin/address/Manager.css'

], function(QUI, QUIControl, QUIButton, QUILoader, QUIConfirm, QUIFormUtils, Grid, Ajax, Locale)
{
    "use strict";

    var lg = 'quiqqer/intranet';

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

            this.$Grid   = null;
            this.$Header = null;

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

            this.$Header = new Element( 'div', {
                html : '<h2>'+ Locale.get( lg, 'address.manager.header.title' ) +'</h2>'+
                       '<div class="package-intranet-profile-short">' +
                           Locale.get( lg, 'address.manager.header.short' ) +
                       '</div>'
            }).inject( this.$Elm, 'top' ),


            this.$Grid = new Grid(GridContainer, {
                columnModel : [{
                    header    : Locale.get( 'quiqqer/system', 'id' ),
                    dataIndex : 'id',
                    dataType  : 'string',
                    hidden    : true
                }, {
                    header    : Locale.get( 'quiqqer/system', 'salutation' ),
                    dataIndex : 'salutation',
                    dataType  : 'string',
                    width     : 60
                }, {
                    header    : Locale.get( 'quiqqer/system', 'firstname' ),
                    dataIndex : 'firstname',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( 'quiqqer/system', 'lastname' ),
                    dataIndex : 'lastname',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( 'quiqqer/system', 'company' ),
                    dataIndex : 'company',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( 'quiqqer/system', 'street' ),
                    dataIndex : 'street_no',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( 'quiqqer/system', 'zip' ),
                    dataIndex : 'zip',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( 'quiqqer/system', 'city' ),
                    dataIndex : 'city',
                    dataType  : 'string',
                    width     : 100
                }, {
                    header    : Locale.get( 'quiqqer/system', 'country' ),
                    dataIndex : 'country',
                    dataType  : 'string',
                    width     : 100
                }],
                buttons : [{
                    name      : 'addAddress',
                    text      : Locale.get( lg, 'address.manager.buttons.add' ),
                    textimage : 'fa fa-plus icon-plus',
                    events    :
                    {
                        onClick : function() {
                            self.openCreateAddress();
                        }
                    }
                }, {
                    name      : 'editAddress',
                    text      : Locale.get( lg, 'address.manager.buttons.edit'),
                    textimage : 'fa fa-edit icon-edit',
                    disabled  : true,
                    events    :
                    {
                        onClick : function() {
                            self.openCreateAddress( self.$Grid.getSelectedData()[ 0 ].id );
                        }
                    }
                }, {
                    name      : 'deleteAddress',
                    text      : Locale.get( lg, 'address.manager.buttons.delete' ),
                    textimage : 'fa fa-trash icon-trash',
                    disabled  : true,
                    events    :
                    {
                        onClick : function() {
                            self.openDeleteAddress( self.$Grid.getSelectedData()[ 0 ].id );
                        }
                    }
                }],
                height : size.y - this.$Header.getSize().y
            });

            this.refresh(function() {
                self.fireEvent( 'load', [ self ] );
            });

            this.$Grid.addEvents({
                onClick : function()
                {
                    self.$Grid.getButtons().each(function(Btn) {
                        Btn.enable();
                    });
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

                var buttons = self.$Grid.getButtons();

                for ( var i = 0, len = buttons.length; i < len; i++ )
                {
                    switch ( buttons[ i ].getAttribute( 'name' ) )
                    {
                        case 'editAddress':
                        case 'deleteAddress':
                            buttons[ i ].disable();
                        break;
                    }
                }

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

            var size       = this.$Elm.getSize(),
                gridHeight = size.y - this.$Header.getSize().y;

            if ( gridHeight < 160 ) {
                gridHeight = 160;
            }

            this.$Grid.setHeight( gridHeight );
            this.$Grid.resize();
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

                    // template header & description
                    if ( edit )
                    {
                        var Header = new Element('h2', {
                            html : Locale.get( 'quiqqer/intranet', 'address.manager.create.sheet.edit.header' )
                        }).inject( Content, 'top' );

                        new Element('div', {
                            html : Locale.get( 'quiqqer/intranet', 'address.manager.create.sheet.edit.desc' )
                        }).inject( Header, 'after' );

                    } else
                    {
                        var Header = new Element('h2', {
                            html : Locale.get( 'quiqqer/intranet', 'address.manager.create.sheet.create.header' )
                        }).inject( Content, 'top' );

                        new Element('div', {
                            html : Locale.get( 'quiqqer/intranet', 'address.manager.create.sheet.create.desc' )
                        }).inject( Header, 'after' );
                    }

                    // template description

                    Buttons.set( 'html', '' );
                    Buttons.setStyles({
                        textAlign : 'right'
                    });

                    new QUIButton({
                        text      : Locale.get( lg, 'address.manager.create.sheet.button.cancel' ),
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

                    var localeEdit   = Locale.get( lg, 'address.manager.create.sheet.button.edit' ),
                        localeCreate = Locale.get( lg, 'address.manager.create.sheet.button.create' );

                    new QUIButton({
                        text      : edit ? localeEdit : localeCreate,
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

                                self.createAddress( data, function()
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
         * Open the delete dialoge
         *
         * @param {String} aid - Address-ID
         */
        openDeleteAddress : function(aid)
        {
            var self = this;

            new QUIConfirm({
                title     : Locale.get( lg, 'address.manager.delete.window.title' ),
                autoclose : false,
                events    :
                {
                    onOpen : function(Win)
                    {
                        Win.Loader.show();

                        self.getAddressDisplay(aid, function(display)
                        {
                            Win.getContent().set({
                                html : Locale.get( lg, 'address.manager.delete.window.text', {
                                    address : display
                                })
                            });

                            Win.getContent().addClass( 'intranet-address-window-delete' );

                            Win.Loader.hide();
                        });
                    },

                    onSubmit : function(Win)
                    {
                        Win.Loader.show();

                        self.deleteAddress(aid, function(display)
                        {
                            Win.close();
                            self.refresh();
                        });
                    }
                }
            }).open();
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
         * Return an address display
         *
         * @param {Integer} aid - Address-ID
         * @param {Function} callback - callback function
         */
        getAddressDisplay : function(aid, callback)
        {
            Ajax.get('package_quiqqer_intranet_ajax_address_display', callback, {
                'package' : 'quiqqer/intranet',
                aid       : aid
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
        },

        /**
         * Delete an address
         *
         * @param {Integer} aid - Address ID
         * @param {Function} callback - [optional] callback function
         */
        deleteAddress : function(aid, callback)
        {
            Ajax.get('package_quiqqer_intranet_ajax_address_delete', function()
            {
                if ( typeof callback !== 'undefined' ) {
                    callback();
                }
            }, {
                'package' : 'quiqqer/intranet',
                aid       : aid
            });
        }
    });
});
