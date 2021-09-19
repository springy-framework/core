<?php

/**
 * Mother class for system base classes.
 *
 * @copyright 2021 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 *
 * @version   1.0.0
 */

namespace Springy\Core;

/**
 * SystemBase class for application.
 */
class SystemBase
{
    /** @var self Request globally instance */
    protected static $instance;

    /** @var mixed the controller object */
    protected $controller;

    /**
     * Constructor.
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     *
     * @param array|string $conf the array of configuration or
     *                           the full path name of the configuration file.
     */
    protected function __construct()
    {
    }

    /**
     * Prevents the instance from being cloned (which would create a second instance of it).
     */
    private function __clone()
    {
    }

    /**
     * Prevents from being unserialized (which would create a second instance of it).
     *
     * @SuppressWarnings(UnusedPrivateMethod)
     */
    private function __wakeup()
    {
    }

    /**
     * Tryes to load a full qualified name controller class.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return bool
     */
    public function loadController(string $name, array $parameters): bool
    {
        if (!class_exists($name)) {
            return false;
        }

        // Creates the controller
        $this->controller = new $name($parameters);

        return true;
    }
}
