<?php
/**
 * Container class for dependency injection.
 *
 * @copyright 2014 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Allan Marques <allan.marques@ymail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Container;

use ArrayAccess;
use Closure;
use InvalidArgumentException;

/**
 * Container class for dependency injection.
 */
class DIContainer implements ArrayAccess
{
    /// Constante que indica o tipo do elemento como uma factory
    const TYPE_FACTORY = 'factory';
    /// Constante que indica o tipo do elemento como um parâmetro
    const TYPE_PARAM = 'param';
    /// Constante que indica o tipo do elemento como uma instância compartilhada
    const TYPE_SHARED = 'shared';

    /// Array que armazena as chaves de todos os elemntos registrados no container
    protected $registeredKeys;

    /// Array que armazena os parâmetros registrados no container
    protected $params;
    /// Array que armazena as factories registradas no container
    protected $factories;
    /// Array que armazena as extensões de factories registradas no container
    protected $factoriesExtensions;
    /// Array que armazena as instâncias compartilhadas registradas no container
    protected $sharedInstances;
    /// Array que armazena os factories que se tornarão instâncias  compartilhadas quando chamadas (lazy load)
    protected $sharedInstancesFactories;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->registeredKeys = [];
        $this->params = [];
        $this->factories = [];
        $this->factoriesExtensions = [];
        $this->sharedInstances = [];
        $this->sharedInstancesFactories = [];
    }

    /**
     * Registers a simple type parameter in the container. The parameter can be any numeric, boolean, strings or arrays.
     *
     * @param string|\Closure $key   key of the registered parameter or a treatment function.
     * @param mixed           $value parameter or treatment function that returns the parameter.
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    public function raw($key, $value = null)
    {
        // Se a chave for uma closure então retornar resultado dessa closure (útil para utilizar no modo array)
        if ($key instanceof Closure) {
            return call_user_func($key, $this);
        }

        // Se valor for uma closure, então o parâmetro é o retorno dessa closure.
        if ($value instanceof \Closure) {
            $value = call_user_func($value, $this);
        } elseif (is_object($value)) { // Objetos não são permitidos, exceção disparada
            throw new InvalidArgumentException(
                'The second param may not be an instance of an object. Use the \'instance\' method instead.'
            );
        }

        $this->registeredKeys[$key] = static::TYPE_PARAM;
        $this->params[$key] = $value;

        return $value;
    }

    /**
     * Returns a parameter set with this identifying key.
     *
     * Throws an error if the key is not registered.
     *
     * @param mixed $key
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    public function param($key)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }

        throw new InvalidArgumentException("The '{$key}' key was not registered as a parameter.");
    }

    /**
     * Register a factory service (Closures) in the container.
     *
     * Useful for saving complex object creation routines.
     *
     * @param mixed   $key
     * @param Closure $factory
     *
     * @return void
     */
    public function bind($key, Closure $factory)
    {
        $this->registeredKeys[$key] = static::TYPE_FACTORY;
        $this->factories[$key] = $factory;
    }

    /**
     * Performs a service and returns the result of the factory and its extensions.
     *
     * @param mixed $key
     * @param array $params
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    public function make($key, array $params = [])
    {
        if (!isset($this->factories[$key])) {
            throw new InvalidArgumentException("The '{$key}' key was not registered as a factory.");
        }

        if (!empty($params)) { // Se houver parâmetros passá-los para a closure do serviço
            $result = call_user_func_array($this->factories[$key], $params);
        } else { // Caso contrário, passar esta instância de container como parâmetro para a closure
            $result = call_user_func($this->factories[$key], $this);
        }

        // Se houver extensões registradas para esta chave identificadora
        if (isset($this->factoriesExtensions[$key])) {
            // Executar todas passando o próprio resultado e esta instância de container como parâmeetro
            foreach ($this->factoriesExtensions[$key] as $extension) {
                $result = call_user_func($extension, $result, $this);
            }
        }

        return $result;
    }

    /**
     * Registers a service extension in the container.
     *
     * @param mixed   $key
     * @param Closure $extension
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function extend($key, Closure $extension)
    {
        if (!isset($this->factories[$key])) {
            throw new InvalidArgumentException("The '{$key}' key was not registered as a factory.");
        }

        $this->factoriesExtensions[$key][] = $extension;
    }

    /**
     * Registers a class instance in the container to be shared by the container consumers.
     *
     * @param string|Closure $key
     * @param mixed          $instance
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    public function instance($key, $instance = null)
    {
        // Se chave for uma closure, esta é executada e seu resultado é retornado (útil para ser usado com o modo array do container)
        if ($key instanceof Closure) {
            return call_user_func($key, $this);
        }

        $this->registeredKeys[$key] = static::TYPE_SHARED;

        if ($instance instanceof Closure) { // Se instância for uma closure, então a instância a ser registrada será o resutado dessa closure.
            $this->sharedInstancesFactories[$key] = $instance;

            return;
        } elseif (!is_object($instance)) { // Somente instâncias de classes são permitidas, exceção é disparada
            throw new InvalidArgumentException('The argument passed is not an instance of an object.');
        }

        $this->sharedInstances[$key] = $instance;

        return $instance;
    }

    /**
     * Returns a shared instance registered with this identifying key.
     *
     * @param mixed $key
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function shared($key)
    {
        if (isset($this->sharedInstancesFactories[$key])) { // Lazy loading as instancias compartilhadas.
            $this->sharedInstances[$key] = call_user_func($this->sharedInstancesFactories[$key], $this);

            unset($this->sharedInstancesFactories[$key]);
        }

        if (isset($this->sharedInstances[$key])) {
            return $this->sharedInstances[$key];
        }

        throw new InvalidArgumentException("The '{$key}' key was not registered as a shared instance.");
    }

    /**
     * Checks if the container has a certain registered name.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Returns the dependency registered by the entered key.
     *
     * @param mixed $key
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    public function resolve($key)
    {
        if (!isset($this->registeredKeys[$key])) {
            throw new InvalidArgumentException("The '{$key}' key was not registered as a dependency.");
        }

        switch ($this->registeredKeys[$key]) {
            case static::TYPE_FACTORY:
                return $this->make($key);

            case static::TYPE_SHARED:
                return $this->shared($key);

            case static::TYPE_PARAM:
            default:
                return $this->param($key);
        }
    }

    /**
     * Removes an element registered in the container.
     *
     * @param mixed $key
     *
     * @return void
     */
    public function forget($key)
    {
        unset($this[$key]);
    }

    /**
     * Whether a offset exists.
     *
     * @param mixed $offset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->registeredKeys[$offset]);
    }

    /**
     * Retrieves de offset.
     *
     * @param mixed $offset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->resolve($offset);
    }

    /**
     * Sets a offset.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($this->has($offset)) { // Se elemento já existe, é substituido.
            $this->forget($offset);
        }

        if ($value instanceof Closure) { // Se for uma closure, faz o bind
            $this->bind($offset, $value);

            return;
        }

        if (is_object($value)) { // Se é uma instância
            $this->instance($offset, $value);

            return;
        }

        // Parâmetro
        $this->raw($offset, $value);
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        switch ($this->registeredKeys[$offset]) {
            case static::TYPE_FACTORY:
                unset($this->factories[$offset]);
                unset($this->factoriesExtensions[$offset]);
                break;
            case static::TYPE_SHARED:
                unset($this->sharedInstances[$offset]);
                break;
            case static::TYPE_PARAM:
                unset($this->params[$offset]);
        }

        unset($this->registeredKeys[$offset]);
    }

    /**
     * Helper to return a new instance.
     *
     * Useful for chaining.
     *
     * @return ArrayUtils
     */
    public static function newInstance()
    {
        return new static();
    }
}
