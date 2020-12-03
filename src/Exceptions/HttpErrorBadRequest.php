<?php

/**
 * HTTP 400 Bad Request error.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Exceptions;

use Throwable;

/**
 * HTTP 400 Bad Request error class.
 */
class HttpErrorBadRequest extends HttpError
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
        string $message = 'Bad Request',
        Throwable $previous = null,
        ?int $code = E_USER_ERROR,
        string $file = null,
        int $line = null
    ) {
        parent::__construct(400, $message, $previous, $code, $file, $line);
    }
}
