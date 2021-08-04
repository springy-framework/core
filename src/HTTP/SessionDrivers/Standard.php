<?php

/**
 * Driver for standard session store handler.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP\SessionDrivers;

/**
 * Driver for standard session store handler.
 */
class Standard implements SessionDriverInterface
{
    /** @var array the session data array */
    protected $data;
    /** @var string the session name */
    protected $domain;
    /** @var string the session id */
    protected $sessId;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->data = [];
        $this->domain = config_get('session.domain', '');
    }

    /**
     * Checks whether a session variable is set.
     *
     * @param string $name
     *
     * @return bool
     */
    public function defined(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Unsets a session variable.
     *
     * @param string $name
     *
     * @return void
     */
    public function forget(string $name)
    {
        if (!$this->defined($name)) {
            return;
        }

        unset($this->data[$name]);
        unset($_SESSION['_'][$name]);
    }

    /**
     * Gets the session id.
     *
     * @return string
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Gets a session variable.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return void
     */
    public function get(string $name, $default = null)
    {
        return $this->data[$name] ?? $default;
    }

    /**
     * Sets a session variable.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $name, $value = null)
    {
        $this->data[$name] = $value;
        $_SESSION['_'][$name] = $value;
    }

    /**
     * Sets the session id.
     *
     * @param string $id
     *
     * @return void
     */
    public function setId(string $sessId)
    {
        session_id($sessId);
    }

    /**
     * Starts the session.
     *
     * @return bool
     */
    public function start(): bool
    {
        session_set_cookie_params(
            0,
            '/',
            $this->domain,
            config_get('session.secure', true),
            true
        );

        $started = session_start();
        $this->data = $_SESSION['_'] ?? [];
        $this->sessId = session_id();

        return $started;
    }
}
