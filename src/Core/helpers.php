<?php
/**
 * Helper file - Functions and constants.
 *
 * Let's make the developer happier and more productive.
 *
 * @copyright 2014 Fernando Val
 * @author    Allan Marques - allan.marques@ymail.com
 * @author    Fernando Val <fernando@fval.com.br>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

// Definig the constantes
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('LF')) {
    define('LF', "\n");
}

/**
 * Get shared container application instance.
 *
 * Returns the shared instance of the application container
 * or a registered service with the name passed by parameter.
 *
 * @param string|Closure $service
 *
 * @return mixed
 */
function app($service = null)
{
    if ($service) {
        return app()->resolve($service);
    }

    return Springy\Core\Application::sharedInstance();
}

/**
 * Gets a application configuration var.
 *
 * @param string $key
 *
 * @return mixed
 */
function config_get(string $key)
{
    return Springy\Core\Kernel::getInstance()->configuration()->get($key);
}

/**
 * A var_dump and die help function.
 *
 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
 * @SuppressWarnings(PHPMD.ExitExpression)
 *
 * @param mixed $var
 * @param bool  $die
 *
 * @return void
 */
function dd($var, $die = true)
{
    echo '<pre>';
    var_dump($var);
    echo '</pre>';

    if ($die) {
        die;
    }
}

/**
 * A helper to set a debug data.
 *
 * @param mixed $data
 * @param bool  $revert
 * @param bool  $saveBacktrace
 * @param int   $backtraceLimit
 *
 * @return void
 */
function debug(
    $data,
    bool $revert = true,
    bool $saveBacktrace = true,
    int $backtraceLimit = 3
) {
    Springy\Core\Debug::getInstance()->add($data, $revert, $saveBacktrace, $backtraceLimit, 1);
}
