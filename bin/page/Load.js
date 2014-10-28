
/**
 * Intranet Loading - page onload event
 *
 * @module package/quiqqer/intranet/bin/page/Load
 * @author www.pcsg.de (Henning Leutz)
 */

define(function()
{
    "use strict";

    if ( typeof QUIQQER_LOGIN_FAILED === 'undefined' ) {
        return;
    }

    if ( !QUIQQER_LOGIN_FAILED ) {
        return;
    }

    require(['qui/QUI'], function(QUI)
    {
        QUI.getMessageHandler(function(MH) {
            MH.addError( QUIQQER_LOGIN_FAILED );
        });
    });

});