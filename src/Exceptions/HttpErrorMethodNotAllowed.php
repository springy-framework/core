<?php

/**
 * HTTP 405 Method Not Allowed error.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Exceptions;

/**
 * HTTP 405 Method Not Allowed error class.
 */
class HttpErrorMethodNotAllowed extends HttpError
{
    /**
     * Constructor.
     *
     * @param string    $message
     * @param Throwable $previous
     * @param int       $code
     * @param int|null  $code
     * @param string    $file
     * @param int       $line
     */
    public function __construct(
        string $message = 'Method Not Allowed',
        \Throwable $previous = null,
        ?int $code = E_USER_ERROR,
        string $file = null,
        int $line = null
    ) {
        parent::__construct(405, $message, $previous, $code, $file, $line);
    }
}
