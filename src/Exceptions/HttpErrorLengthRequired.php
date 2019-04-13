<?php
/**
 * Springy HTTP 411 Length Required error class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Exceptions;

class HttpErrorLengthRequired extends HttpError
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
    public function __construct(string $message = 'Length Required', Throwable $previous = null, ?int $code = E_USER_ERROR, string $file = null, int $line = null)
    {
        parent::__construct(411, $message, $previous, $code, $file, $line);
    }
}
