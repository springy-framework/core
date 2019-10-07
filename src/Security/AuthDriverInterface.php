<?php

/**
 * Interface to standardize identity authentication drivers.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Security;

/**
 * Interface to standardize identity authentication drivers.
 */
interface AuthDriverInterface
{
    /**
     * Returns the session identity used to perform the authentication.
     *
     * @return IdentityInterface
     */
    public function getDefaultIdentity(): IdentityInterface;

    /**
     * Returns the identity by the ID that identifies it.
     *
     * @param string|int $iid
     *
     * @return IdentityInterface
     */
    public function getIdentityById($iid): IdentityInterface;

    /**
     * Returns the identity identifier of the identity session.
     *
     * @return string
     */
    public function getIdentitySessionKey(): string;

    /**
     * Returns the last session identity to pass successfully through authentication.
     *
     * @return IdentityInterface
     */
    public function getLastValidIdentity(): IdentityInterface;

    /**
     * Checks whether the current identity login and password are valid.
     *
     * @param string $login
     * @param string $password
     *
     * @return bool
     */
    public function isValid(string $login, string $password): bool;

    /**
     * Defines the session identity that will be used to perform authentication.
     *
     * @param IdentityInterface $identity
     *
     * @return void
     */
    public function setDefaultIdentity(IdentityInterface $identity);
}
