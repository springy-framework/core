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

use Springy\Core\Debug;

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
        self::$instance = $this;
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

    /**
     * Sends the response header and content to default outpou.
     *
     * @param bool $debug
     *
     * @return void
     */
    public function send(bool $debug = false)
    {
        $this->header()->send();

        if ($debug) {
            echo Debug::getInstance()->inject(self::$body);

            return;
        }

        echo self::$body;
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
