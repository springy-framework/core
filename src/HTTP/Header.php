<?php

/**
 * HTTP Header helper.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

/**
 * HTTP Header helper.
 */
class Header
{
    /** @var array the list of headers to be sent */
    protected $headers;
    /** @var int the HTTP response code */
    protected $httpResponseCode;

    /**
     * Constructor.
     *
     * @param int $httpResponseCode
     */
    public function __construct($httpResponseCode = 200)
    {
        $this->headers = [];
        $this->httpResponseCode = $httpResponseCode;
    }

    /**
     * Send HTTP header if was not sent yet.
     *
     * @param string $header
     * @param string $value
     * @param bool   $replace
     *
     * @return void
     */
    protected function header(string $header, string $value, bool $replace = true)
    {
        if ($replace) {
            $this->headers[$header] = [];
        }

        $this->headers[$header][] = $value;
    }

    /**
     * Sends Cache-Control HTTP header.
     *
     * @param string $value
     * @param bool   $replace
     *
     * @return void
     */
    public function cacheControl(string $value, bool $replace = true)
    {
        $this->header('Cache-Control', $value, $replace);
    }

    /**
     * Clears the headers.
     *
     * @return void
     */
    public function clear()
    {
        $this->headers = [];
    }

    /**
     * Sends Content-Type HTTP header.
     *
     * @param string $type
     * @param string $charset
     * @param bool   $replace
     *
     * @return void
     */
    public function contentType(string $type = 'text/html', string $charset = 'UTF-8', bool $replace = true)
    {
        $this->header('Content-Type', $type . '; charset=' . $charset, $replace);
    }

    /**
     * Sends Expires HTTP header.
     *
     * @param string $value
     * @param bool   $replace
     *
     * @return void
     */
    public function expires(string $value)
    {
        $this->header('Expires', $value, true);
    }

    public function getContentType()
    {
        $header = $this->getHeader('Content-Type');

        if (count($header)) {
            $cType = explode('; ', $header[0])[0];

            return $cType;
        }

        return '';
    }

    public function getHeader(string $header): array
    {
        return $this->headers[$header] ?? [];
    }

    public function headers(): array
    {
        $headers = [];

        foreach ($this->headers as $string => $values) {
            foreach ($values as $value) {
                $headers[] = $string . ': ' . $value;
            }
        }

        return $headers;
    }

    public function httpResponseCode(int $httpResponseCode = null): int
    {
        if ($httpResponseCode !== null) {
            $this->httpResponseCode = $httpResponseCode;
        }

        return $this->httpResponseCode;
    }

    /**
     * Checks whether the header array is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->headers);
    }

    /**
     * Sends Last-Modified HTTP header.
     *
     * @param string $value
     * @param bool   $replace
     *
     * @return void
     */
    public function lastModified(string $value)
    {
        $this->header('Last-Modified', $value, true);
    }

    /**
     * Sets http response code to 404 - page not found.
     *
     * @return void
     */
    public function notFound()
    {
        $this->httpResponseCode = 404;
    }

    /**
     * Sends Pragma HTTP header.
     *
     * @param string $value
     * @param bool   $replace
     *
     * @return void
     */
    public function pragma(string $value, bool $replace = true)
    {
        $this->header('Pragma', $value, $replace);
    }

    /**
     * Sends the HTTP headers.
     *
     * @return bool
     */
    public function send(): bool
    {
        if (headers_sent()) {
            return false;
        }

        if ($this->isEmpty()) {
            $this->contentType();
        }

        foreach ($this->headers as $string => $values) {
            $first = true;

            foreach ($values as $value) {
                header($string . ': ' . $value, $first, $this->httpResponseCode);
                $first = false;
            }
        }

        return true;
    }
}
