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
    /** @var Header the HTTP header object */
    protected $header;
    /** @var string the body content */
    protected $body;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->header = new Header();
        $this->body = '';
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
}
