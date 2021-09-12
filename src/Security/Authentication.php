<?php

/**
 * Authentication identity manager.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.1.0
 */

namespace Springy\Security;

use Springy\HTTP\Cookie;
use Springy\HTTP\Session;

/**
 * Authentication identity manager.
 */
class Authentication
{
    /** @var AuthDriverInterface the authentication driver */
    protected $driver;
    /** @var IdentityInterface the authenticated user object */
    protected $user;

    /**
     * Constructor.
     *
     * @param AuthDriverInterface $driver
     */
    public function __construct(AuthDriverInterface $driver = null)
    {
        $this->setDriver($driver);

        $this->wakeupSession();
        $this->rememberSession();
    }

    /**
     * Destroys the user session and cookie.
     *
     * @return void
     */
    protected function destroyUserData()
    {
        $session = Session::getInstance();
        $session->set($this->driver->getIdentitySessionKey(), null);
        $session->forget($this->driver->getIdentitySessionKey());

        $cookie = Cookie::getInstance();
        $cookie->set(
            $this->driver->getIdentitySessionKey(),
            '',
            time() - 3600,
            '/',
            config_get('session.domain'),
            config_get('session.secure', true)
        );
        $cookie->delete($this->driver->getIdentitySessionKey());
    }

    /**
     * Restores the user session from cookie if is defined.
     *
     * @return void
     */
    protected function rememberSession()
    {
        $uid = Cookie::getInstance()->get($this->driver->getIdentitySessionKey());

        if ($this->user == null && $uid) {
            $this->loginWithId($uid);
        }
    }

    /**
     * Wakeup the autenticated user from the session.
     *
     * @return void
     */
    protected function wakeupSession()
    {
        $identitySessionData = Session::getInstance()->get($this->driver->getIdentitySessionKey());

        if (is_array($identitySessionData)) {
            $this->user = $this->driver->getDefaultIdentity();

            $this->user->fillFromSession($identitySessionData);
        }
    }

    /**
     * Attempts to log in of the user with your login and password.
     *
     * @param string $login       the user login.
     * @param string $password    the user password.
     * @param bool   $remember    saves remember cookie.
     * @param bool   $saveSession saves in session if successful.
     *
     * @return bool
     */
    public function attempt(
        string $login,
        string $password,
        bool $remember = false,
        bool $saveSession = true
    ): bool {
        if ($this->driver->isValid($login, $password)) {
            if ($saveSession) {
                $this->login($this->driver->getLastValidIdentity(), $remember);
            }

            return true;
        }

        return false;
    }

    /**
     * Checks whether has a logged in user.
     *
     * @return bool
     */
    public function check(): bool
    {
        return $this->user != null;
    }

    /**
     * Returns the authentication driver manager.
     *
     * @return AuthDriverInterface
     */
    public function getDriver(): AuthDriverInterface
    {
        return $this->driver;
    }

    /**
     * Sets user logged in and saves into session.
     *
     * @param IdentityInterface $user
     * @param bool              $remember
     *
     * @return void
     */
    public function login(IdentityInterface $user, bool $remember = false)
    {
        $this->user = $user;

        Session::getInstance()->set(
            $this->driver->getIdentitySessionKey(),
            $this->user->getSessionData()
        );

        if ($remember) {
            Cookie::getInstance()->set(
                $this->driver->getIdentitySessionKey(), // Cookie key
                $this->user->getId(),                   // User id
                5184000,                                // 60 days
                '/',
                config_get('session.domain'),
                config_get('session.secure', true)
            );
        }
    }

    /**
     * Logon the user by its identitification.
     *
     * @param mixed $uid
     * @param bool  $remember
     *
     * @return void
     */
    public function loginWithId($uid, bool $remember = false)
    {
        $identity = $this->driver->getIdentityById($uid);

        if ($identity) {
            $this->login($identity, $remember);
        }
    }

    /**
     * Destroys the users logged session.
     *
     * @return void
     */
    public function logout()
    {
        $this->user = null;

        $this->destroyUserData();
    }

    /**
     * Defines the autentication driver manager.
     *
     * @param AuthDriverInterface $driver
     *
     * @return void
     */
    public function setDriver(AuthDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Returns the logged in user.
     *
     * @return IdentityInterface|null
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Checks the authentication without log in the user.
     *
     * @param atring $login
     * @param atring $password
     *
     * @return bool
     */
    public function validate(string $login, string $password): bool
    {
        return $this->attempt($login, $password, false, false);
    }
}
