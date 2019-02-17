<?php
/**
 * Springy Exception class.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Exceptions;

class Http403Error extends HttpError
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
    public function __construct (string $message = '', int $code = 0, Throwable $previous = NULL)
    {
        parent::__construct($message, 403, $previous);
    }
}
