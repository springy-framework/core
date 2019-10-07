<?php

/**
 * HTTP error.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Exceptions;

use Error;
use Throwable;

/**
 * HTTP error class.
 */
class HttpError extends Error
{
    protected $statusCode;

    /**
     * Constructor.
     *
     * @param int       $statusCode the HTTP status code.
     * @param string    $message
     * @param Throwable $previous
     * @param int       $code
     * @param int|null  $code
     * @param string    $file
     * @param int       $line
     */
    public function __construct(
        int $statusCode,
        string $message = null,
        \Throwable $previous = null,
        ?int $code = E_USER_ERROR,
        string $file = null,
        int $line = null
    ) {
        if (null === $file || null === $line) {
            $dbt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
            $file = $dbt[0]['file'];
            $line = $dbt[0]['line'];
        }

        $this->file = $file;
        $this->line = $line;
        $this->statusCode = $statusCode;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
