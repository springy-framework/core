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
    /** @var string HTTP_X_REQUESTED_WITH value */
    protected static $requestedWith;

    /**
     * Constructor.
     */
    public function __construct()
    {
        self::$method = $_SERVER['REQUEST_METHOD'] ?? null;
        self::$requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    }

    /**
     * Checks whether the request method was an Ajax.
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return strtolower(self::$requestedWith) === 'xmlhttprequest';
    }

    /**
     * Checks whether the request method was a GET.
     *
     * @return bool
     */
    public function isGet(): bool
    {
        return self::$method === 'GET';
    }

    /**
     * Checks whether the request method was a HEAD.
     *
     * @return bool
     */
    public function isHead(): bool
    {
        return self::$method === 'HEAD';
    }

    /**
     * Checks whether the request method was a POST.
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return self::$method === 'POST';
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
