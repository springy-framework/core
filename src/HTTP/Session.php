<?php
/**
 * HTTP session handler class.
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

class Session
{
    /** @var self globally instance */
    protected static $instance;

    /** @var SessionDriverInterface the session driver object */
    protected static $engine;
    /** @var string the session cookie name */
    protected static $name;
    /** @var bool the session is started */
    protected static $started;

    public function __construct()
    {
        if (self::$instance !== null) {
            return;
        }

        self::$started = false;
    }

    protected function checkConfig()
    {
        if (self::$engine === null) {
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
        $sessId = $cookie->get(self::$name);

        // Deletes session cookie if empty session ID
        if ($sessId == '') {
            $cookie->delete(self::$name);
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
     * @param Configuration $config
     *
     * @return self
     */
    public function configure(Configuration $config): self
    {
        if (self::$engine !== null) {
            throw new SpringyException('Session already configurated.');
        }

        self::$name = $config->get('session.name', 'SPSESSID');

        $engine = $config->get('session.engine');
        if ($engine === null) {
            throw new SpringyException('Undefined session engine.');
        }

        $engine = 'Springy\\HTTP\\SessionDrivers\\'.$engine;
        self::$engine = new $engine($config);

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

        return self::$engine->defined($name);
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

        self::$engine->forget($name);
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
        $this->start();

        return self::$engine->get($name, $default);
    }

    /**
     * Gets the session id.
     *
     * @return string
     */
    public function getId(): string
    {
        $this->start();

        return self::$engine->getId();
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

        self::$engine->set($name, $value);
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
        if (self::$started) {
            throw new SpringyException('Can\'t set session id because already started');
        }

        $this->checkConfig();
        self::$engine->setId($sessId);
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
        if (self::$started) {
            return self::$started;
        }

        self::$name = $name ?? self::$name;
        session_name(self::$name);
        $this->checkSession();

        self::$started = self::$engine->start();

        return self::$started;
    }

    /**
     * Returns current instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
