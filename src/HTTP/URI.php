<?php
/**
 * URI handler class.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Lucas Cardozo <lucas.cardozo@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   3.0.0
 */

namespace Springy\HTTP;

class URI
{
    /** @var self URI globally instance */
    protected static $instance;

    /** @var string HTTP host */
    protected static $httpHost;
    /** @var string the URI string without query string parameters */
    protected static $uriString;
    /** @var array the URI segments */
    protected static $segments;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (self::$instance !== null) {
            return;
        }

        self::$segments = [];
        self::$uriString = '';
        self::$httpHost = $this->parseHost();

        if (self::$httpHost === '$') {
            return;
        }

        $this->parseRequestURI();
        $this->parseSegments();
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

        return trim(
            preg_replace(
                '/([^:]+)(:\\d+)?/',
                '$1$2',
                $_SERVER['HTTP_HOST'] ?? ''
            ), ' ..@'
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
        if (trim($path, '/') == '' || $path == '/'.pathinfo(__FILE__, PATHINFO_BASENAME)) {
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
            self::$uriString = $this->parsePathInfo();

            return;
        }

        self::$uriString = explode('?', $_SERVER['REQUEST_URI'])[0];
    }

    /**
     * Explodes the URI string without query string params in an array of segments.
     *
     * @return void
     */
    protected function parseSegments()
    {
        foreach (explode('/', trim(self::$uriString, '/')) as $segment) {
            $segment = trim($segment);

            if ($segment == '') {
                continue;
            }

            self::$segments[] = $segment;
        }
    }

    /**
     * Returns the array of URI segments.
     *
     * @return array
     */
    public function getSegments(): array
    {
        return self::$segments;
    }

    /**
     * Returns the URI string without query string parameters.
     *
     * @return string
     */
    public function getURIString(): string
    {
        return self::$uriString ?? '';
    }

    /**
     * Return the current host with protocol.
     *
     * @return string
     */
    public function host(): string
    {
        return self::$httpHost;
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
