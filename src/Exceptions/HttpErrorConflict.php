<?php

/**
 * HTTP 409 Conflict error.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Exceptions;

use Throwable;

/**
 * HTTP 409 Conflict error class.
 */
class HttpErrorConflict extends HttpError
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
        string $message = 'Conflict',
        Throwable $previous = null,
        ?int $code = E_USER_ERROR,
        string $file = null,
        int $line = null
    ) {
        parent::__construct(409, $message, $previous, $code, $file, $line);
    }
}
