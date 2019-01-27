<?php
/**
 * HTTP request handler class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   3.0.0
 */

namespace Springy\HTTP;

class Request
{
    /** @var self Request globally instance */
    protected static $instance;

    /** @var string HTTP request method */
    protected static $method;

    /**
     * Constructor.
     */
    public function __construct()
    {
        self::$method = $_SERVER['REQUEST_METHOD'] ?? null;
    }

    /**
     * Returns the request method.
     *
     * @return string
     */
    public function method(): string
    {
        return self::$method ?? '';
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
