<?php
/**
 * HTTP Header helper.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

class Header
{
    /** @var array the list of headers to be sent */
    protected $headers;
    /** @var int the HTTP response code */
    protected $httpResponseCode;

    /**
     * Constructor.
     *
     * @param integer $httpResponseCode
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
    private function header(string $header, string $value, bool $replace = true)
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
        $this->header('Content-Type', $type.'; charset='.$charset, $replace);
    }

    /**
     * Sends Expires HTTP header.
     *
     * @param string $value
     * @param bool   $replace
     *
     * @return void
     */
    public function expires(string $value, bool $replace = true)
    {
        $this->header('Expires', $value, $replace);
    }

    public function httpResponseCode(int $httpResponseCode = null): int
    {
        if ($httpResponseCode !== null) {
            $this->httpResponseCode = $httpResponseCode;
        }

        return $this->httpResponseCode;
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

        if (!count($this->headers)) {
            $this->contentType();
        }

        foreach ($this->headers as $string => $values) {
            $first = true;

            foreach ($values as $value) {
                header($string.': '.$value, $first, $this->httpResponseCode);
                $first = false;
            }
        }

        return true;
    }
}
