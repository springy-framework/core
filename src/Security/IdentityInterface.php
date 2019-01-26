<?php
/**
 * Interface for user identity session.
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
 * Interface for user identity session.
 */
interface IdentityInterface
{
    /**
     * Loads the identity data by given credential.
     *
     * This method is executed when the user is loaded by a given array of conditions for a query.
     *
     * @param array $data the array with the condition to load the data.
     *
     * @return void
     */
    public function loadByCredentials(array $data);

    /**
     * Loads the identity class from the session.
     *
     * @param array $data the array with the identity data.
     *
     * @return void
     */
    public function fillFromSession(array $data);

    /**
     * Gets the identity id key.
     *
     * @return string|int
     */
    public function getId();

    /**
     * Gets the identity id column name.
     *
     * @return string
     */
    public function getIdField(): string;

    /**
     * Gets the session key name for the identity.
     *
     * @return string
     */
    public function getSessionKey(): string;

    /**
     * Gets the identity session data.
     *
     * @return array
     */
    public function getSessionData(): array;

    /**
     * Gets the identity credentials.
     *
     * @example Login and password.
     *
     * @return array the array with credential data.
     */
    public function getCredentials(): array;
}
