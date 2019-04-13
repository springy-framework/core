<?php
/**
 * Springy HTTP 404 Not Found error class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Exceptions;

class NotFoundHttpError extends HttpError
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
    public function __construct(string $message = 'Not Found', Throwable $previous = null, ?int $code = E_USER_ERROR, string $file = null, int $line = null)
    {
        parent::__construct(404, $message, $previous, $code, $file, $line);
    }
}
