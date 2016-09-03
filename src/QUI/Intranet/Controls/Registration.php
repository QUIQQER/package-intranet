<?php

/**
 * This file contains \QUI\Intranet\Controls\Registration
 */

namespace QUI\Intranet\Controls;

use QUI;

/**
 * Registration control
 *
 * @author www.pcsg.de (Henning Leutz)
 */
class Registration extends QUI\Control
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        $this->addCSSFile(
            dirname(__FILE__) . '/Registration.css'
        );

        $this->setAttribute(
            'qui-class',
            'package/quiqqer/intranet/bin/Registration'
        );

        $this->setAttribute('class', 'package-intranet-registration');
    }

    /**
     * (non-PHPdoc)
     *
     * @see \QUI\Control::create()
     */
    public function getBody()
    {
        $Engine  = QUI::getTemplateManager()->getEngine();
        $Project = $this->getProject();

        $Engine->assign(array(
            'Project' => $this->getAttribute('Project'),
            'Site'    => $this->getAttribute('Site'),
            'Locale'  => QUI::getLocale()
        ));


        // user loged in check
        $Plugin = QUI::getPluginManager()->get('quiqqer/intranet');

        $loggedIn = $Plugin->getSettings(
            'registration',
            'loggedInDuringRegistrationn'
        );

        if (!$loggedIn && QUI::getUserBySession()->getId()) {
            return $Engine->fetch(
                dirname(__FILE__) . '/RegistrationLogedIn.html'
            );
        }


        // AGB
        $result = $Project->getSites(array(
            'where' => array(
                'type' => 'quiqqer/intranet:registration/termsOfUse'
            ),
            'limit' => 1
        ));

        if (isset($result[0])) {
            $Engine->assign('Site_TermsAndConditions', $result[0]);
        }


        // Datenschutz
        $result = $Project->getSites(array(
            'where' => array(
                'type' => 'quiqqer/intranet:registration/privacy'
            ),
            'limit' => 1
        ));

        if (isset($result[0])) {
            $Engine->assign('Site_Privacy', $result[0]);
        }

        return $Engine->fetch(dirname(__FILE__) . '/Registration.html');
    }
}
