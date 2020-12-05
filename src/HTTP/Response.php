<?php

/**
 * HTTP response handler.
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

/**
 * HTTP response handler class.
 */
class Response
{
    /**
     * Based on Symfony Response constants.
     */
    public const HTTP_CONTINUE = 100;
    public const HTTP_SWITCHING_PROTOCOLS = 101;
    public const HTTP_PROCESSING = 102;            // RFC2518
    public const HTTP_EARLY_HINTS = 103;           // RFC8297
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_ACCEPTED = 202;
    public const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    public const HTTP_NO_CONTENT = 204;
    public const HTTP_RESET_CONTENT = 205;
    public const HTTP_PARTIAL_CONTENT = 206;
    public const HTTP_MULTI_STATUS = 207;          // RFC4918
    public const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    public const HTTP_IM_USED = 226;               // RFC3229
    public const HTTP_MULTIPLE_CHOICES = 300;
    public const HTTP_MOVED_PERMANENTLY = 301;
    public const HTTP_FOUND = 302;
    public const HTTP_SEE_OTHER = 303;
    public const HTTP_NOT_MODIFIED = 304;
    public const HTTP_USE_PROXY = 305;
    public const HTTP_RESERVED = 306;
    public const HTTP_TEMPORARY_REDIRECT = 307;
    public const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_PAYMENT_REQUIRED = 402;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    public const HTTP_NOT_ACCEPTABLE = 406;
    public const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const HTTP_REQUEST_TIMEOUT = 408;
    public const HTTP_CONFLICT = 409;
    public const HTTP_GONE = 410;
    public const HTTP_LENGTH_REQUIRED = 411;
    public const HTTP_PRECONDITION_FAILED = 412;
    public const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    public const HTTP_REQUEST_URI_TOO_LONG = 414;
    public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    public const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    public const HTTP_EXPECTATION_FAILED = 417;
    public const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    public const HTTP_MISDIRECTED_REQUEST = 421;                                         // RFC7540
    public const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    public const HTTP_LOCKED = 423;                                                      // RFC4918
    public const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918

    /**
     * @deprecated
     */
    public const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    public const HTTP_TOO_EARLY = 425;                                                   // RFC-ietf-httpbis-replay-04
    public const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    public const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    public const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    public const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    public const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;
    public const HTTP_NOT_IMPLEMENTED = 501;
    public const HTTP_BAD_GATEWAY = 502;
    public const HTTP_SERVICE_UNAVAILABLE = 503;
    public const HTTP_GATEWAY_TIMEOUT = 504;
    public const HTTP_VERSION_NOT_SUPPORTED = 505;
    public const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    public const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    public const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    public const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    public const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

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
        if (is_null(self::$instance)) {
            new self();
        }

        return self::$instance;
    }
}
