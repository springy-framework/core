<?php

/**
 * HTTP cookie handler.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   2.0.0
 */

namespace Springy\HTTP;

/**
 * HTTP cookie handler class.
 */
class Cookie
{
    /** @var self globally instance */
    protected static $instance;

    /**
     * Constructor.
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     */
    private function __construct()
    {
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
     * Converts the cookie name to string.
     *
     * @param mixed $key
     *
     * @return string
     */
    protected function scrubKey($key): string
    {
        if (!is_array($key)) {
            return (string) $key;
        }

        return key($key) . '[' . current($key) . ']';
    }

    /**
     * Converts the cookie name to array if it is in scrubed.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    protected function unscrubKey($key)
    {
        $matches = [];

        if (
            !is_array($key)
            && preg_match('/([\w\d]+)\[([\w\d]+)\]$/i', $key, $matches)
        ) {
            $key = [$matches[1] => $matches[2]];
        }

        return $key;
    }

    /**
     * Deletes a cookie.
     *
     * The expiration of the cookie will be set to -1 day
     * to force browser deletion.
     *
     * @param string $name
     *
     * @return void
     */
    public function delete($name)
    {
        if (!$this->exists($name)) {
            return;
        }

        $key = $this->unscrubKey($name);

        // Check for key array
        if (is_array($key)) {
            // Grab key/value pair
            $cooKey = key($key);
            $cooVal = current($key);

            $this->set([$cooKey => $cooVal], '', -86400);
            unset($_COOKIE[$cooKey][$cooVal]);

            return;
        }

        // Check for cookie array
        if (is_array($_COOKIE[$key])) {
            foreach ($_COOKIE[$key] as $cooKey => $cooVal) {
                $this->delete([$key => $cooKey]);
            }

            unset($_COOKIE[$key]);

            return;
        }

        $this->set($key, '', -86400);
        unset($_COOKIE[$key]);
    }

    /**
     * Checks if a cookie exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function exists($name): bool
    {
        $key = $this->unscrubKey($name);

        if (is_array($key)) {
            // Grab key/value pair
            $cooKey = key($key);
            $cooVal = current($key);

            return isset($_COOKIE[$cooKey][$cooVal]);
        }

        return isset($_COOKIE[$key]);
    }

    /**
     * Gets a cookie data.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        // Change string representation array to key/value array
        $key = $this->unscrubKey($name);

        if (is_array($key)) {
            // Grab key/value pair
            $cooKey = key($key);
            $cooVal = current($key);

            return $_COOKIE[$cooKey][$cooVal] ?? null;
        }

        return $_COOKIE[$key] ?? null;
    }

    /**
     * Sets a cookie data.
     *
     * @param string $name     the cookie name.
     * @param string $value    value for the cookie.
     * @param int    $expire   the time the cookie expires.
     * @param string $path     the path on the server in which the cookie will
     *                         be available on.
     * @param string $domain   the (sub)domain that the cookie is available to.
     * @param bool   $secure   indicates that the cookie should only be
     *                         transmitted over a secure HTTPS connection
     *                         from the client.
     * @param bool   $httponly when TRUE the cookie will be made accessible only
     *                         through the HTTP protocol.
     *
     * @return void
     */
    public function set(
        $name,
        string $value = '',
        int $expire = 0,
        string $path = '',
        string $domain = '',
        bool $secure = false,
        bool $httponly = true
    ) {
        return setcookie(
            $this->scrubKey($name),
            $value,
            $expire ? time() + $expire : 0,
            $path,
            $domain,
            $secure,
            $httponly
        );
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
