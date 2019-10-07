<?php

/**
 * HTTP session handler.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   3.0.0
 */

namespace Springy\HTTP;

use Springy\Core\Configuration;
use Springy\Exceptions\SpringyException;

/**
 * HTTP session handler class.
 */
class Session
{
    /** @var self globally instance */
    protected static $instance;

    /** @var SessionDriverInterface the session driver object */
    protected $engine;
    /** @var string the session cookie name */
    protected $name;
    /** @var bool the session is started */
    protected $started;

    /**
     * Constructor.
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     */
    private function __construct()
    {
        $this->started = false;
        self::$instance = $this;
    }

    /**
     * Prevents the instance from being cloned (which would create a second instance of it).
     */
    private function __clone()
    {
    }

    /**
     * Prevents from being unserialized (which would create a second instance of it).
     *
     * @SuppressWarnings(UnusedPrivateMethod)
     */
    private function __wakeup()
    {
    }

    /**
     * Checks whether the session was configured.
     *
     * @return void
     */
    protected function checkConfig()
    {
        if ($this->engine === null) {
            throw new SpringyException('Session not configurated yet.');
        }
    }

    /**
     * Checks the session id due to Safari empty bug.
     *
     * @return void
     */
    protected function checkSession()
    {
        $cookie = Cookie::getInstance();
        $sessId = $cookie->get($this->name);

        // Deletes session cookie if empty session ID
        if ($sessId == '') {
            $cookie->delete($this->name);
        }

        // Reset session id if it is invalid
        if ($sessId == '' || preg_match('/([^A-Za-z0-9\-]+)/', $sessId)) {
            $sessId = substr(md5(uniqid(mt_rand(), true)), 0, 26);

            session_id($sessId);
        }
    }

    /**
     * Configures the session handler.
     *
     * @return self
     */
    public function configure(): self
    {
        $config = Configuration::getInstance();

        if ($this->engine !== null) {
            throw new SpringyException('Session already configurated.');
        }

        $this->name = $config->get('session.name', 'SPSESSID');

        $engine = $config->get('session.engine');
        if ($engine === null) {
            throw new SpringyException('Undefined session engine.');
        }

        $engine = 'Springy\\HTTP\\SessionDrivers\\' . $engine;
        $this->engine = new $engine();

        return self::getInstance();
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
        $this->start();

        return $this->engine->defined($name);
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
        $this->start();
        $this->engine->forget($name);
    }

    /**
     * Gets a session variable.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        $this->start();

        return $this->engine->get($name, $default);
    }

    /**
     * Gets the session id.
     *
     * @return string
     */
    public function getId(): string
    {
        $this->start();

        return $this->engine->getId();
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
        $this->start();
        $this->engine->set($name, $value);
    }

    /**
     * Sets the session id.
     *
     * @param string $sessId
     *
     * @return void
     */
    public function setId(string $sessId)
    {
        if ($this->started) {
            throw new SpringyException('Can\'t set session id because already started');
        }

        $this->checkConfig();
        $this->engine->setId($sessId);
    }

    /**
     * Starts the session.
     *
     * @param string $name
     *
     * @return bool
     */
    public function start(string $name = null): bool
    {
        if ($this->started) {
            return $this->started;
        }

        if ($this->engine === null) {
            $this->configure();
        }

        $this->name = $name ?? $this->name;
        session_name($this->name);
        $this->checkSession();

        $this->started = $this->engine->start();

        return $this->started;
    }

    /**
     * Returns current instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            new self();
        }

        return self::$instance;
    }
}
