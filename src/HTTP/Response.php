<?php
/**
 * HTTP response handler class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

class Response
{
    /** @var self globally instance */
    protected static $instance;

    /** @var Header the HTTP header object */
    protected static $header;
    /** @var string the body content */
    protected static $body;

    /**
     * Constructor.
     */
    public function __construct()
    {
        self::$header = new Header();
        self::$body = '';
    }

    /**
     * Get or set the body content string.
     *
     * @return string
     */
    public function body(string $content = null): string
    {
        if ($content !== null) {
            self::$body = $content;
        }

        return self::$body;
    }

    /**
     * Returns the internal Header object.
     *
     * @return Header
     */
    public function header(): Header
    {
        return self::$header;
    }

    public function notFound()
    {
        self::$header->notFound();
    }

    public function send()
    {}

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
