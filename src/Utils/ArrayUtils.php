<?php
/**
 * Arrays manipulations utilities.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.2.0
 */

namespace Springy\Utils;

/**
 * Arrays manipulations utilities.
 */
class ArrayUtils
{
    /**
     * Adds a value to the array ONLY if there is no value to the given key.
     *
     * @param array $array
     * @param mixed $key
     * @param mixed $value
     *
     * @return array
     */
    public function add(array $array, $key, $value): array
    {
        if (!isset($array[$key])) {
            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Makes another array with values filtered by the callback.
     *
     * @param array    $array
     * @param \Closure $callback
     *
     * @return array
     */
    public function make(array $array, \Closure $callback): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            list($filteredKey, $filteredValue) = call_user_func($callback, $key, $value);

            $results[$filteredKey] = $filteredValue;
        }

        return $results;
    }

    /**
     * Creates an array with all the values of a given key in a multidimensional associative array.
     *
     * @param array $array
     * @param mixed $value key of desired values.
     * @param mixed $key   key of the value to be used as key of the elements of the new array.
     *
     * @return array
     */
    public function pluck(array $array, $value, $key = null): array
    {
        $results = [];

        foreach ($array as $item) {
            $itemValue = is_object($item) ? $item->{$value} : $item[$value];

            if ($key === null) {
                $results[] = $itemValue;

                continue;
            }

            $itemKey = is_object($item) ? $item->{$key} : $item[$key];
            $results[$itemKey] = $itemValue;
        }

        return $results;
    }

    /**
     * Returns an multidimentional array with keys and values of the given array.
     *
     * The keys will be in index 0 and values in index 1.
     *
     * @param array $array
     *
     * @return array
     */
    public function split(array $array): array
    {
        return [array_keys($array), array_values($array)];
    }

    /**
     * Returns an array with the values of the keys passed by parameter.
     *
     * @param array $array
     * @param array $keys
     *
     * @return array
     */
    public function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Returns an array with all values except those that have the passed keys per parameter.
     *
     * @param array $array
     * @param array $keys
     *
     * @return array
     */
    public function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * Returns an array with the data ordered by the callback function.
     *
     * @param array    $array
     * @param \Closure $callback
     *
     * @return array
     */
    public function sort(array $array, \Closure $callback): array
    {
        uasort($array, $callback);

        return $array;
    }

    /**
     * Returns the FIRST value passed in the test function or the default value.
     *
     * @param array    $array
     * @param \Closure $callback
     * @param mixed    $default
     *
     * @return mixed
     */
    public function firstThatPasses(array $array, \Closure $callback, $default = null)
    {
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Retorna o ÚLTIMO valor que passar na função teste ou o valor padrão.
     *
     * @param array    $array
     * @param \Closure $callback
     * @param mixed    $default
     *
     * @return mixed
     */
    public function lastThatPasses(array $array, \Closure $callback, $default = null)
    {
        return $this->firstThatPasses(array_reverse($array), $callback, $default);
    }

    /**
     * Returns an array with all values that pass in the test function.
     *
     * @param array    $array
     * @param \Closure $callback
     *
     * @return array
     */
    public function allThatPasses(array $array, \Closure $callback): array
    {
        $filtered = [];

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Returns a one-dimensional array with all values in a multidimensional array.
     *
     * @param array $array
     *
     * @return array
     */
    public function flatten(array $array): array
    {
        $results = [];

        array_walk_recursive($array, function ($val) use (&$results) {
            $results[] = $val;
        });

        return $results;
    }

    /**
     * Returns a one-dimensional array with multidimensional values joined in a key with dot notation.
     *
     * Example:
     *
     * $array['key1']['key2] --> $array['key1.key2'].
     *
     * @param array  $array
     * @param string $prepend
     *
     * @return array
     */
    public function dottedMake(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, $this->dottedMake($value, $prepend.$key.'.'));

                continue;
            }

            $results[$prepend.$key] = $value;
        }

        return $results;
    }

    /**
     * Returns the value of an array using dot notation.
     *
     * @param array       $array
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function dottedGet(array $array, $key = null, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Extracts an element from an array, using the dot notation, and returns its value.
     *
     * @param array  $array
     * @param string $key
     *
     * @return mixed
     */
    public function dottedPull(array &$array, string $key)
    {
        $value = $this->dottedGet($array, $key);

        $this->dottedUnset($array, $key);

        return $value;
    }

    /**
     * Inserts an element into an array using dot notation.
     *
     * @param array       $array
     * @param string|null $key
     * @param mixed       $value
     *
     * @return void
     */
    public function dottedSet(array &$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;
    }

    /**
     * Removes the element from an array using dot notation.
     *
     * @param array  $array
     * @param string $key
     *
     * @return void
     */
    public function dottedUnset(array &$array, string $key)
    {
        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                return;
            }

            $array = &$array[$key];
        }

        unset($array[array_shift($keys)]);
    }

    /**
     * Returns a one-dimensional array containing the values of a multidimensional array of the key in the dot notation format.
     *
     * @param array  $array
     * @param string $key
     *
     * @return array
     */
    public function dottedFetch(array $array, string $key): array
    {
        foreach (explode('.', $key) as $segment) {
            $results = [];

            foreach ($array as $value) {
                $value = (array) $value;

                $results[] = $value[$segment];
            }

            $array = array_values($results);
        }

        return array_values($results);
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
