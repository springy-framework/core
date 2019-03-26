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

use Springy\Core\Configuration;
use Springy\Core\Debug;

class Response
{
    /** @var self globally instance */
    protected static $instance;

    /** @var Header the HTTP header object */
    protected $header;
    /** @var string the body content */
    protected $body;

    /**
     * Constructor.
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     */
    private function __construct()
    {
        $this->header = new Header();
        $this->body = '';
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
     * Get or set the body content string.
     *
     * @return string
     */
    public function body(string $content = null): string
    {
        if ($content !== null) {
            $this->body = $content;
        }

        return $this->body;
    }

    /**
     * Returns the internal Header object.
     *
     * @return Header
     */
    public function header(): Header
    {
        return $this->header;
    }

    public function notFound()
    {
        $this->header->notFound();
    }

    /**
     * Sends the response header and content to default outpou.
     *
     * @return void
     */
    public function send()
    {
        $this->header()->send();

        if (Configuration::getInstance()->get('application.debug')) {
            echo Debug::getInstance()->inject($this->body);

            return;
        }

        echo $this->body;
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
