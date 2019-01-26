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
