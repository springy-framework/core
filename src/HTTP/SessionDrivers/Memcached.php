<?php

/**
 * Driver for store session in MemcacheD service.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP\SessionDrivers;

use Springy\Core\Configuration;
use Springy\Exceptions\SpringyException;
use Springy\HTTP\Cookie;

/**
 * Driver for store session in MemcacheD service.
 */
class Memcached extends Standard implements SessionDriverInterface
{
    /** @var int session expiration time */
    protected $expires;
    /** @var string the memcached server host */
    protected $host;
    /** @var int|string the memcached server port */
    protected $port;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $config = Configuration::getInstance();
        $this->expires = $config->get('session.expires', 1440);
        $this->host = $config->get('session.host', 'localhost');
        $this->port = $config->get('session.port', 11211);

        if (!class_exists('\Memcached')) {
            throw new SpringyException('Memcached connection not supported.');
        }
    }

    /**
     * Gets the Memcached object.
     *
     * @return void
     */
    protected function getMemcacheD()
    {
        $memcached = new \Memcached();
        $memcached->addServer($this->host, $this->port);

        $name = 'testkey';
        $ttl = 10;
        $data = sha1(time());
        $memcached->set($name, $data, $ttl);
        $res = $memcached->get($name);
        if ($res != $data) {
            throw new SpringyException('Can\'t connect to memcached.');
        }

        return $memcached;
    }

    /**
     * Saves the session data into memcached server.
     *
     * @return void
     */
    public function saveSession()
    {
        $memcached = $this->getMemcacheD();
        $memcached->set(
            session_name() . '_' . session_id(),
            $this->data,
            $this->expires
        );
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
    }

    /**
     * Starts the session.
     *
     * @return bool
     */
    public function start(): bool
    {
        Cookie::getInstance()->set(
            session_name(),
            session_id(),
            0,
            '/',
            $this->domain,
            false,
            false
        );

        $memcached = $this->getMemcacheD();
        $this->data = $memcached->get(session_name() . '_' . session_id()) ?? [];

        register_shutdown_function([$this, 'saveSession']);

        return true;
    }
}
