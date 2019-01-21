<?php
/**
 * Event management mediator.
 *
 * @copyright 2014 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Allan Marques <allan.marques@ymail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Events;

use Springy\Container\DIContainer;

/**
 * Event management mediator.
 */
class Mediator
{
    /// Container para injeção de dependência de objetos
    protected $container;

    /// Array que armazena os handlers registrados
    protected $handlers;
    /// Array que armazena os handlers masters (wildcards)
    protected $masterHandlers;
    /// Array que armazena todos os handlers ordenados por ordem de prioridade
    protected $orderedHandlers;
    /// Armazena o valor do evento atualmente sendo disparado
    protected $currentEvent;

    /**
     * Constructor.
     *
     * @param DIContainer $container
     */
    public function __construct(DIContainer $container = null)
    {
        $this->container = $container ?: new DIContainer();
        $this->handlers = [];
        $this->masterHandlers = [];
        $this->orderedHandlers = [];
    }

    /**
     * Sets the dependency injection container.
     *
     * @param DIContainer $container
     *
     * @return void
     */
    public function setContainer(DIContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Gets the dependency injection container.
     *
     * @return DIContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Register a handler for handling events.
     *
     * @param string|array $events   name or set of names that represent the event.
     * @param mixed        $handler  object, closure, or depenence name that will handle the event.
     * @param int          $priority priority of the handler in the execution stack, larger, more important.
     *
     * @return void
     */
    public function registerHandlerFor($events, $handler, $priority = 0)
    {
        foreach ((array) $events as $event) { // Para cada nom de evento
            if (strpos($event, '.*') !== false) { // Se houver '*' é um masterHandler
                // Registra-o e retorna
                $this->registerMasterHandler($event, $handler);
                continue;
            }

            // Registra o handler de acordo com sua prioridade
            $this->handlers[$event][$priority][] = $this->resolveHandler($handler);

            // Reseta a ordem de prioridade dos handlers
            unset($this->orderedHandlers[$event]);
        }
    }

    /**
     * Alias for registerHandlerFor().
     *
     * @param string|array $events   name or set of names that represent the event.
     * @param mixed        $handler  object, closure, or depenence name that will handle the event.
     * @param int          $priority priority of the handler in the execution stack, larger, more important.
     *
     * @return void
     */
    public function on($event, $handler, $priority = 0)
    {
        $this->registerHandlerFor($event, $handler, $priority);
    }

    /**
     * Checks whether handlers exist for the event.
     *
     * @param string $event
     *
     * @return bool
     */
    public function hasHandlersFor(string $event)
    {
        return isset($this->handlers[$event]);
    }

    /**
     * Removes all handlers from an event.
     *
     * @param string $event
     *
     * @return void
     */
    public function forget(string $event)
    {
        unset($this->handlers[$event]);
        unset($this->orderedHandlers[$event]);
    }

    /**
     * Alias for forget().
     *
     * @param string $event
     *
     * @return void
     */
    public function off(string $event)
    {
        $this->forget($event);
    }

    /**
     * Throws an event.
     *
     * @param string $event
     * @param array  $data  data to be passed to handlers.
     *
     * @return mixed
     */
    public function fire(string $event, array $data = [])
    {
        // Transforma dados em array se não for (para trabalhar com call_user_func_array() facilmente
        if (!is_array($data)) {
            $data = [$data];
        }

        if ($this->hasHandlersFor($event)) {
            $responses = [];

            $this->currentEvent = $event;

            foreach ($this->getHandlersFor($event) as $handler) {
                $res = call_user_func_array($handler, $data);

                // Se retorno da execução do handler for exatamente igual a falso, interrompe a corrente de handlers
                if ($res === false) {
                    break;
                }

                $responses[] = $res;
            }

            $this->currentEvent = null;

            return $responses;
        }
    }

    /**
     * The event being fired at the moment.
     *
     * @return string|null
     */
    public function current()
    {
        return $this->currentEvent;
    }

    /**
     * Registers a class handler as subscriber.
     *
     * @param mixed $handler
     *
     * @return void
     */
    public function subscribe($handler)
    {
        if (is_string($handler)) { // Se string, nome de dependencia
            $handler = $this->container[$handler]; // resolver dependencia
        }

        $handler->subscribes($this);
    }

    /**
     * Resolves handler type.
     *
     * @param mixed $handler
     *
     * @return Closure
     */
    protected function resolveHandler($handler)
    {
        if (is_string($handler)) { // Se string, nome de dependencia
            return $this->createHandler($handler); // resolver dependencia
        }

        return $handler;
    }

    /**
     * Creates a handler.
     *
     * The handler must be a string in format object@action.
     *
     * Example: 'cache@store'
     *
     * @param string $handler
     *
     * @return Closure
     */
    protected function createHandler(string $handler)
    {
        $container = $this->container;

        return function () use ($handler, $container) {
            $parts = explode('@', $handler);

            // If there is no action, the default is 'handle'
            $method = count($parts) == 2 ? $parts[1] : 'handle';

            // Creates callable as event handler
            $service = [$container[$parts[0]], $method];

            return call_user_func_array($service, func_get_args());
        };
    }

    /**
     * It registers a 'master' handler symbolized by a '*' in its composition.
     *
     * This handler will have priority over all 'sub-handlers'.
     *
     * @param string  $event
     * @param Closure $handler
     *
     * @return void
     */
    protected function registerMasterHandler($event, $handler)
    {
        $this->masterHandlers[$this->getMasterHandlerKey($event)][] = $this->resolveHandler($handler);
    }

    /**
     * Extracts the name of the event in which the master handler will be 'listening'
     *
     * @param string $event
     *
     * @return string
     */
    protected function getMasterHandlerKey(string $event): string
    {
        $parts = explode('*', $event);

        return $parts[0];
    }

    /**
     * Returns the handlers for the requested event
     *
     * @param string $event
     *
     * @return array
     */
    protected function getHandlersFor(string $event): array
    {
        if (!isset($this->orderedHandlers[$event])) {
            $this->orderHandlersFor($event);
        }

        return array_merge(
            $this->orderedHandlers[$event],
            $this->getMasterHandlersFor($event)
        );
    }

    /**
     * Returns the masters handlers for the indicated event
     *
     * @param string $event
     *
     * @return array
     */
    protected function getMasterHandlersFor(string $event): array
    {
        $masterHandlers = [];

        foreach ($this->masterHandlers as $masterKey => $handlers) {
            if (strpos($event, $masterKey) === 0) { //Se nome do master handler estiver contido no nomedo do evento
                $masterHandlers = array_merge($masterHandlers, $handlers);
            }
        }

        return $masterHandlers;
    }

    /**
     * Sorts the handlers according to their priorities.
     *
     * @param string $event
     *
     * @return void
     */
    protected function orderHandlersFor(string $event)
    {
        $sorted = $this->handlers[$event];

        krsort($sorted, SORT_NUMERIC);

        $this->orderedHandlers[$event] = call_user_func_array('array_merge', $sorted);
    }

    /**
     * Helper to return a new instance.
     *
     * Useful for chaining.
     *
     * @return Mediator
     */
    public static function newInstance(DIContainer $container = null)
    {
        return new static($container);
    }
}
