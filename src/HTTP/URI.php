<?php

/**
 * URI handler.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Lucas Cardozo <lucas.cardozo@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   3.0.0
 */

namespace Springy\HTTP;

/**
 * URI handler class.
 */
class URI
{
    /** @var self URI globally instance */
    protected static $instance;

    /** @var string HTTP host */
    protected $httpHost;
    /** @var string the URI string without query string parameters */
    protected $uriString;
    /** @var array the URI segments */
    protected $segments;

    /**
     * Constructor.
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     */
    private function __construct()
    {
        $this->segments = [];
        $this->uriString = '';
        $this->httpHost = $this->parseHost();
        self::$instance = $this;

        if ($this->httpHost === '$') {
            return;
        }

        $this->parseRequestURI();
        $this->parseSegments();
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
     * Parses the $_SERVER['HTTP_HOST] variable.
     *
     * @return string
     */
    protected function parseHost(): string
    {
        if (php_sapi_name() === 'cli') {
            return '$';
        }

        return preg_replace(
            '/([^:]+)(:\\d+)?/',
            '$1',
            $_SERVER['HTTP_HOST'] ?? ''
        ) . (
            ($_SERVER['SERVER_PORT'] ?? 80) != 80
            ? ':' . $_SERVER['SERVER_PORT']
            : ''
        );
    }

    /**
     * Parses the $_SERVER['ORIG_PATH_INFO'], if has.
     *
     * @return string
     */
    protected function parsePathInfo(): string
    {
        $path = $_SERVER['ORIG_PATH_INFO'] ?? @getenv('ORIG_PATH_INFO');
        if (trim($path, '/') == '' || $path == '/' . pathinfo(__FILE__, PATHINFO_BASENAME)) {
            return '';
        }

        return str_replace($_SERVER['SCRIPT_NAME'] ?? '', '', $path);
    }

    /**
     * Parses the $_SERVER['REQUEST_URI'], if has.
     *
     * @return void
     */
    protected function parseRequestURI()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            $this->uriString = $this->parsePathInfo();

            return;
        }

        $this->uriString = explode('?', rawurldecode($_SERVER['REQUEST_URI']))[0];
    }

    /**
     * Explodes the URI string without query string params in an array of segments.
     *
     * @return void
     */
    protected function parseSegments()
    {
        foreach (explode('/', trim($this->uriString, '/')) as $segment) {
            $segment = trim($segment);

            if ($segment == '') {
                continue;
            }

            $this->segments[] = $segment;
        }
    }

    /**
     * Returns the current host with port number but without protocol.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->httpHost;
    }

    /**
     * Returns the array of URI segments.
     *
     * @return array
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    /**
     * Returns the URI string without query string parameters.
     *
     * @return string
     */
    public function getUriString(): string
    {
        return $this->uriString ?? '';
    }

    /**
     * Returns the current URL with protocol and port number.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return sprintf(
            'http%s://%s%s',
            ($_SERVER['HTTPS'] ?? '') == 'on' ? 's' : '',
            $this->httpHost,
            rtrim($this->uriString, '/')
        );
    }

    /**
     * Returns current instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            new self();
        }

        return self::$instance;
    }
}
