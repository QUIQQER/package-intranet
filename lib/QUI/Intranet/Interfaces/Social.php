<?php

/**
 * This file contains \QUI\Intranet\Interfaces\Social
 */

namespace QUI\Intranet\Interfaces;

/**
 * Interface for social media registration / login
 * @author www.pcsg.de (Henning Leutz)
 */

interface Social
{
    /**
     * Return the user by a google token
     *
     * @param String $token
     * @throws \QUI\Exception
     * @return \QUI\Users\User
     */
    public function getUserByToken($token);

    /**
     * Check if the user is
     *
     * @param String $token
     * @return false|\QUI\Users\User
     */
    public function isAuth($token);

    /**
     * Checks if the token is correct
     *
     * @param String $token
     */
    public function checkToken($token);
}