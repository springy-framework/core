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
    protected static $body;
    /** @var string HTTP request method */
    protected static $method;
    /** @var string the received body in raw format */
    protected static $rawBody;
    /** @var string HTTP_X_REQUESTED_WITH value */
    protected static $requestedWith;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (static::$instance !== null) {
            return;
        }

        self::$method = $_SERVER['REQUEST_METHOD'] ?? null;
        self::$rawBody = $this->getRawData();
        self::$body = $this->parseRawData();
        self::$requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        self::$instance = $this;
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
        $encoding = mb_detect_encoding(self::$rawBody, 'auto');
        if ($encoding != 'UTF-8') {
            self::$rawBody = iconv($encoding, 'UTF-8', self::$rawBody);
        }

        $request = json_decode(self::$rawBody);

        return $request;
    }

    /**
     * Returns received body.
     *
     * @return object|null
     */
    public function getBody()
    {
        return self::$body;
    }

    /**
     * Returns the request method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return self::$method ?? '';
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
     * Checks whether the request method was a DELETE.
     *
     * @return bool
     */
    public function isDelete(): bool
    {
        return self::$method === 'DELETE';
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
     * Checks whether the request method was a OPTIONS.
     *
     * @return bool
     */
    public function isOptions()
    {
        return self::$method === 'OPTIONS';
    }

    /**
     * Checks whether the request method was a PATCH.
     *
     * @return bool
     */
    public function isPatch()
    {
        return self::$method === 'PATCH';
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
     * Checks whether the request method was a PUT.
     *
     * @return bool
     */
    public function isPut(): bool
    {
        return self::$method === 'PUT';
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
