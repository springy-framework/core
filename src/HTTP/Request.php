<?php

/**
 * HTTP request handler.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

/**
 * HTTP request handler class.
 */
class Request
{
    /** @var self Request globally instance */
    protected static $instance;

    /** @var array HTTP request headers */
    protected $headers;
    /** @var object|null HTTP request body in Json format */
    protected $jsonBody;
    /** @var int the code of error occurred during parse body */
    protected $jsonError;
    /** @var string the message of error occurred during parse body */
    protected $jsonErrorMsg;
    /** @var string HTTP request method */
    protected $method;
    /** @var string the received body in raw format */
    protected $rawBody;
    /** @var string HTTP_X_REQUESTED_WITH value */
    protected $requestedWith;
    /** @var string Bearer Token value */
    protected $bearerToken;

    /**
     * Constructor.
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     */
    final private function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? null;
        $this->headers = $this->parseHeaders();
        $this->rawBody = $this->getRawData();
        $this->jsonBody = $this->parseRawData();
        $this->jsonError = json_last_error();
        $this->jsonErrorMsg = json_last_error_msg();
        $this->requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        $this->bearerToken = $this->parseBearerToken();
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
     * Gets all request headers.
     *
     * @return array
     */
    protected function parseHeaders()
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if ($headers !== false) {
                return $headers;
            }
        }

        foreach ($_SERVER as $name => $value) {
            if (
                (substr($name, 0, 5) == 'HTTP_')
                || ($name == 'CONTENT_TYPE')
                || ($name == 'CONTENT_LENGTH')
                || ($name == 'AUTHORIZATION')
            ) {
                $headers[
                    str_replace(
                        [' ', 'Http'],
                        ['-', 'HTTP'],
                        ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))
                    )
                ] = $value;
            }
        }

        return $headers;
    }

    /**
     * Gets raw body data.
     *
     * @return mixed
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

        return json_decode($this->rawBody);
    }

    /**
     * Parses the bearer token authorization header.
     *
     * @return string|null
     */
    protected function parseBearerToken(): ?string
    {
        $headers = $this->getHeaders();

        if (!isset($headers['Authorization'])) {
            return null;
        }

        return trim(str_replace('Bearer', '', $headers['Authorization']));
    }

    /**
     * Returns the bearer token.
     *
     * @return string|null
     */
    public function getBearerToken(): ?string
    {
        return $this->bearerToken ?? null;
    }

    /**
     * Returns received body.
     *
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->rawBody;
    }

    /**
     * Returns all request headers.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns received body.
     *
     * @return object|null
     */
    public function getJsonBody(): ?object
    {
        return $this->jsonBody;
    }

    /**
     * Returns the code of the error occurred during body encoding.
     *
     * @return int
     */
    public function getJsonError(): int
    {
        return $this->jsonError;
    }

    /**
     * Returns the message of the error occurred during body encoding.
     *
     * @return string
     */
    public function getJsonErrorMsg(): string
    {
        return $this->jsonErrorMsg;
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
     * Returns the request method checking if has Method Override.
     *
     * @return string
     */
    public function getMethodOverride(): string
    {
        if (
            $this->isPost()
            && isset($this->headers['X-HTTP-Method-Override'])
            && in_array($this->headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'])
        ) {
            return $this->headers['X-HTTP-Method-Override'];
        }

        return $this->getMethod();
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
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
