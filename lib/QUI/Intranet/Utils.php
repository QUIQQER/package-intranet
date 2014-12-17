<?php

/**
 * This file contains \QUI\Intranet\Utils
 */

namespace QUI\Intranet;

use \QUI\Utils\DOM;
use \QUI\Utils\XML;
use \QUI\Utils\System\File as QUIFile;

/**
 * Intranet utils - little helper for intranet
 *
 * @author www.pcsg.de (Henning Leutz)
 */

class Utils
{
    /**
     * Return the extra profile categories from other plugins
     * search intranet.xml
     *
     * @return Array
     */
    static function getProfileExtendCategories()
    {
        $packages = QUIFile::readDir( OPT_DIR );
        $result   = array();

        foreach ( $packages as $package )
        {
            if ( $package == 'composer' ) {
                continue;
            }

            $package_dir = OPT_DIR .'/'. $package;
            $list        = QUIFile::readDir( $package_dir );


            foreach ( $list as $sub )
            {
                if ( !is_dir( $package_dir .'/'. $sub ) ) {
                    continue;
                }

                $xmlFile = $package_dir .'/'. $sub .'/intranet.xml';

                if ( !file_exists( $xmlFile ) ) {
                    continue;
                }

                // read intranet xml
                $items = XML::getMenuItemsXml( $xmlFile );

                foreach ( $items as $Item )
                {
                    /* @var $Item \DOMElement */
                    $result[] = array(
                        'text'    => DOM::getTextFromNode( $Item ),
                        'name'    => $Item->getAttribute( 'name' ),
                        'icon'    => DOM::parseVar( $Item->getAttribute( 'icon' ) ),
                        'require' => $Item->getAttribute( 'require' ),
                        'exec'    => $Item->getAttribute( 'exec' ),
                    );
                }
            }
        }

        return $result;
    }
}
