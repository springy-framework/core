<?php
/**
 * Springy HTTP 503 Service unavailable class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Exceptions;

class Http503Error extends HttpError
{
    /**
     * Constructor.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param string    $message
     * @param int       $code     the code will be replaced by 403 HTTP forbidden error.
     * @param Throwable $previous
     */
    public function __construct(string $message = 'Service Unavailable', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 503, $previous);
    }
}
