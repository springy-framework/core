<?php

/**
 * Interface to standardize the base of the application.
 *
 * @copyright 2021 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 *
 * @version   1.0.0
 */

namespace Springy\Core;

/**
 * Interface to standardize the base of the application.
 */
interface SystemInterface
{
    /**
     * Tries to finds the controller for the application.
     *
     * @return bool
     */
    public function findController(): bool;

    /**
     * Sends controller not found message or error.
     *
     * @return void
     */
    public function notFound(): void;
}
