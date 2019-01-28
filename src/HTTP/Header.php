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
    /**
     * Constructor.
     *
     * @param array $headers
     */
    public function __construct(array $headers = [])
    {
        foreach ($headers as $header => $replace) {
            if (!$this->header($header, $replace)) {
                break;
            }
        }
    }

    /**
     * Send HTTP header if was not sent yet.
     *
     * @param string $header
     * @param bool   $replace
     * @param int    $httpResposeCode
     *
     * @return bool
     */
    private function header(string $header, bool $replace = true, int $httpResposeCode = null): bool
    {
        if (headers_sent()) {
            return false;
        }

        header($header, $replace, $httpResposeCode);

        return true;
    }

    /**
     * Sends Cache-Control HTTP header.
     *
     * @param string $value
     * @param bool   $replace
     *
     * @return bool
     */
    public function cacheControl(string $value, bool $replace = true): bool
    {
        return $this->header('Cache-Control: '.$value, $replace);
    }

    /**
     * Sends Expires HTTP header.
     *
     * @param string $value
     * @param bool   $replace
     *
     * @return bool
     */
    public function expires(string $value, bool $replace = true): bool
    {
        return $this->header('Expires: '.$value, $replace);
    }

    /**
     * Sends Pragma HTTP header.
     *
     * @param string $value
     * @param bool   $replace
     *
     * @return bool
     */
    public function pragma(string $value, bool $replace = true): bool
    {
        return $this->header('Pragma: '.$value, $replace);
    }

    /**
     * Sends Content-Type HTTP header.
     *
     * @param string $type
     * @param string $charset
     * @param bool   $replace
     *
     * @return bool
     */
    public function contentType(string $type = 'text/html', string $charset = 'UTF-8', bool $replace = true): bool
    {
        return $this->header('Content-Type: '.$type.'; charset='.$charset, $replace);
    }
}
