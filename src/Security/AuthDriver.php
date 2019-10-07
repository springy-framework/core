<?php

/**
 * Authentication driver.
 *
 * @copyright 2019 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Security;

/**
 * Authentication driver.
 */
class AuthDriver implements AuthDriverInterface
{
    /** @var HasherInterface the password hasher handler */
    protected $hasher;
    /** @var IdentityInterface the identity object */
    protected $identity;
    /** @var IdentityInterface last valid identity object */
    protected $lastValidIdentity;

    public function __construct(HasherInterface $hasher = null, IdentityInterface $identity = null)
    {
        $this->setHasher($hasher);
        $this->setDefaultIdentity($identity);
    }

    /**
     * Returns the session identity used to perform the authentication.
     *
     * @return IdentityInterface
     */
    public function getDefaultIdentity(): IdentityInterface
    {
        return $this->identity;
    }

    /**
     * Gets the password hasher object.
     *
     * @return HasherInterface
     */
    public function getHasher(): HasherInterface
    {
        return $this->hasher;
    }

    /**
     * Returns the identity by the ID that identifies it.
     *
     * @param string|int $iid
     *
     * @return IdentityInterface
     */
    public function getIdentityById($iid): IdentityInterface
    {
        $idField = $this->identity->getIdField();
        $this->identity->loadByCredentials([$idField => $iid]);

        return $this->identity;
    }

    /**
     * Returns the identity identifier of the identity session.
     *
     * @return string
     */
    public function getIdentitySessionKey(): string
    {
        return $this->identity->getSessionKey();
    }

    /**
     * Returns the last session identity to pass successfully through authentication.
     *
     * @return IdentityInterface
     */
    public function getLastValidIdentity(): IdentityInterface
    {
        return $this->lastValidIdentity;
    }

    /**
     * Checks whether the current identity login and password are valid.
     *
     * @param string $login
     * @param string $password
     *
     * @return bool
     */
    public function isValid(string $login, string $password): bool
    {
        // $appInstance = Application::sharedInstance();
        // Throws login attempt event
        // $appInstance->fire('auth.attempt', [$login, $password]);

        $credentials = $this->identity->getCredentials();
        $this->identity->loadByCredentials([$credentials['login'] => $login]);
        $validPassword = $this->identity->{$credentials['password']};

        if ($this->hasher->verify($password, $validPassword)) {
            $this->lastValidIdentity = clone $this->identity;

            // Throws auth success event
            // $appInstance->fire('auth.success', [$this->lastValidIdentity]);

            return true;
        }

        // Throws auth fail event
        // $appInstance->fire('auth.fail', [$login, $password]);

        return false;
    }

    /**
     * Defines the session identity that will be used to perform authentication.
     *
     * @param IdentityInterface $identity
     *
     * @return void
     */
    public function setDefaultIdentity(IdentityInterface $identity)
    {
        $this->identity = $identity;
    }

    /**
     * Sets the password hasher object handler.
     *
     * @param HasherInterface $hasher
     *
     * @return void
     */
    public function setHasher(HasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
}
