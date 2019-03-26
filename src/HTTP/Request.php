<?php
/**
 * HTTP request handler class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

class Request
{
    /** @var self Request globally instance */
    protected static $instance;

    /** @var object|null HTTP request body */
    protected $body;
    /** @var string HTTP request method */
    protected $method;
    /** @var string the received body in raw format */
    protected $rawBody;
    /** @var string HTTP_X_REQUESTED_WITH value */
    protected $requestedWith;

    /**
     * Constructor.
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     */
    private function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? null;
        $this->rawBody = $this->getRawData();
        $this->body = $this->parseRawData();
        $this->requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
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
     * Gets raw body data.
     *
     * @return void
     */
    protected function getRawData()
    {
        if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            return $GLOBALS['HTTP_RAW_POST_DATA'];
        }

        return file_get_contents('php://input');
    }

    /**
     * Parses the raw body data and returns a decoded JSon.
     *
     * @return object|null
     */
    protected function parseRawData()
    {
        $encoding = mb_detect_encoding($this->rawBody, 'auto');
        if ($encoding != 'UTF-8') {
            $this->rawBody = iconv($encoding, 'UTF-8', $this->rawBody);
        }

        $request = json_decode($this->rawBody);

        return $request;
    }

    /**
     * Returns received body.
     *
     * @return object|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Returns the request method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method ?? '';
    }

    /**
     * Checks whether the request method was an Ajax.
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return strtolower($this->requestedWith) === 'xmlhttprequest';
    }

    /**
     * Checks whether the request method was a DELETE.
     *
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->method === 'DELETE';
    }

    /**
     * Checks whether the request method was a GET.
     *
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    /**
     * Checks whether the request method was a HEAD.
     *
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->method === 'HEAD';
    }

    /**
     * Checks whether the request method was a OPTIONS.
     *
     * @return bool
     */
    public function isOptions()
    {
        return $this->method === 'OPTIONS';
    }

    /**
     * Checks whether the request method was a PATCH.
     *
     * @return bool
     */
    public function isPatch()
    {
        return $this->method === 'PATCH';
    }

    /**
     * Checks whether the request method was a POST.
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Checks whether the request method was a PUT.
     *
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->method === 'PUT';
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
