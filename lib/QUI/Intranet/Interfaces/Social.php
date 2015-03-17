<?php

/**
 * This file contains \QUI\Intranet\Interfaces\Social
 */

namespace QUI\Intranet\Interfaces;

use \QUI\Users\User;

/**
 * Interface for social media registration / login
 * @author www.pcsg.de (Henning Leutz)
 */

interface Social
{
    /**
     * Checks if the token is correct
     *
     * @param String $token
     */
    public function checkToken($token);

    /**
     * Return the user by a google token
     *
     * @param String $token
     * @throws \QUI\Exception
     * @return \QUI\Users\User
     */
    public function getUserByToken($token);

    /**
     * Return the user data from the social network
     *
     * @param string $token
     * @return Array
     */
    public function getUserDataByToken($token);

    /**
     * has the user registered with the social network
     *
     * @param \QUI\Users\User $User
     * @return Bool
     */
    public function hasAccess(User $User);

    /**
     * Check if the user is
     *
     * @param String $token
     * @return false|\QUI\Users\User
     */
    public function isAuth($token);

    /**
     * Login the user
     *
     * @param String $token - token hash
     * @return \QUI\Users\User
     */
    public function login($token);

    /**
     * This method executed if the user is created
     * So, the social media can set some extra fields to the user
     *
     * @param \QUI\Users\User $User
     * @param String $token
     */
    public function onRegistration(User $User, $token);
}