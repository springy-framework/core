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
 * Returns the application environment.
 *
 * @return string
 */
function app_env(): string
{
    return Springy\Core\Configuration::getInstance()->getEnvironment();
}

/**
 * Returns the application name.
 *
 * @return string
 */
function app_name(): string
{
    return Springy\Core\Kernel::getInstance()->getApplicationName();
}

/**
 * Returns the application version.
 *
 * @return string
 */
function app_version(): string
{
    return Springy\Core\Kernel::getInstance()->getApplicationVersion();
}

/**
 * Gets an application configuration var.
 *
 * @param string $key
 * @param mixed  $default
 *
 * @return mixed
 */
function config_get(string $key, $default = null)
{
    return Springy\Core\Configuration::getInstance()->get($key, $default);
}

/**
 * Sets an application configuration var.
 *
 * @param string $key
 * @param mixed  $val
 *
 * @return void
 */
function config_set(string $key, $val)
{
    return Springy\Core\Configuration::getInstance()->set($key, $val);
}

/**
 * Returns the current URL.
 *
 * @return string
 */
function current_host(): string
{
    return ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_PORT'] ?? '')
        .(($_SERVER['SERVER_PORT'] ?? '80') != 80 ? ':'.$_SERVER['SERVER_PORT'] : '');
}

/**
 * Returns the current URL.
 *
 * @return string
 */
function current_url(): string
{
    return 'http'.(($_SERVER['HTTPS'] ?? '') == 'on' ? 's' : '')
        .'://'
        .($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_PORT'] ?? '')
        .(($_SERVER['SERVER_PORT'] ?? '80') != 80 ? ':'.$_SERVER['SERVER_PORT'] : '')
        .($_SERVER['REQUEST_URI'] ?? '');
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
 * Gets an environment variable.
 *
 * @param string $key
 * @param mixed  $default
 *
 * @return mixed
 */
function env(string $key, $default = null)
{
    $value = getenv($key);

    if ($value === false) {
        return $default;
    }

    if (($vLength = strlen($value)) > 1 && $value[0] === '"' && $value[$vLength - 1] === '"') {
        return substr($value, 1, -1);
    }

    return $value;
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
