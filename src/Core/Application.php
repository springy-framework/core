<?php

/**
 * Application dependency container.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Core;

use Springy\Container\DIContainer;
use Springy\Events\Mediator;

/**
 * Application dependency container.
 */
class Application extends DIContainer
{
    /// Intância compartilhada desta classe.
    protected static $sharedInstance;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->bindDefaultDependencies();
    }

    /**
     * Registers an event handler with the given priority.
     *
     * @SuppressWarnings(PHPMD.ShortMethod)
     *
     * @param string $event
     * @param mixed  $handler
     * @param int    $priority
     *
     * @return Application
     */
    public function on(string $event, $handler, int $priority = 0)
    {
        $this->resolve('events')->on($event, $handler, $priority);

        return $this;
    }

    /**
     * Removes the record from an event handler.
     *
     * @param string $event
     *
     * @return Application
     */
    public function off(string $event)
    {
        $this->resolve('events')->off($event);

        return $this;
    }

    /**
     * Throws the event.
     *
     * @param string $event
     * @param array  $data
     *
     * @return mixed
     */
    public function fire(string $event, array $data = [])
    {
        return $this->resolve('events')->fire($event, $data);
    }

    /**
     * Returns the shared instance of this class.
     *
     * @return Application
     */
    public static function sharedInstance()
    {
        if (!static::$sharedInstance) {
            static::$sharedInstance = new static(); // @phpstan-ignore-line
        }

        return static::$sharedInstance;
    }

    /**
     * Registers the default dependencies of the application.
     *
     * @return void
     */
    protected function bindDefaultDependencies()
    {
        $this->instance('events', new Mediator($this));
    }
}
