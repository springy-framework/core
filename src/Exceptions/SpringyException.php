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

use RuntimeException;

class SpringyException extends RuntimeException
{
    /** @var array|null error context */
    protected $context;

    /**
     * Constructor.
     *
     * @param int        $code
     * @param string     $message
     * @param string     $file
     * @param int        $line
     * @param array|null $context
     */
    public function __construct(
        string $message = null,
        int $code = E_USER_ERROR,
        string $file = '',
        int $line = 0,
        array $context = null
    ) {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
    }

    /**
     * Returns the error context.
     *
     * @return array|null
     */
    public function getContext()
    {
        return $this->context;
    }
}
