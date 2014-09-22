<?php

/**
 * This file contains \QUI\Intranet\Utils
 */

namespace QUI\Intranet;

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
        $packages_dir = OPT_DIR;
        $packages     = \QUI\Utils\System\File::readDir( OPT_DIR );
        $result       = array();

        foreach ( $packages as $package )
        {
            if ( $package == 'composer' ) {
                continue;
            }

            $package_dir = OPT_DIR .'/'. $package;
            $list        = \QUI\Utils\System\File::readDir( $package_dir );


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
                $items = \QUI\Utils\XML::getMenuItemsXml( $xmlFile );


                foreach ( $items as $Item )
                {
                    $result[] = array(
                        'text'    => \QUI\Utils\DOM::getTextFromNode( $Item ),
                        'name'    => $Item->getAttribute( 'name' ),
                        'icon'    => \QUI\Utils\DOM::parseVar( $Item->getAttribute( 'icon' ) ),
                        'require' => $Item->getAttribute( 'require' ),
                        'exec'    => $Item->getAttribute( 'exec' ),
                    );
                }
            }
        }

        return $result;
    }
}