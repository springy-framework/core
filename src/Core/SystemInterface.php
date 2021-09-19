<?php

namespace Springy\Core;

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
