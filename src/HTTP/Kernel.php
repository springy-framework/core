<?php
/**
 * Kernel for the web application requisition.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

use Springy\Core\Kernel as MainKernel;
use Springy\Exceptions\Http404Error;
use Springy\Security\Authentication;

class Kernel extends MainKernel
{
    /**
     * Calls the controller endpoint.
     *
     * @param array $arguments
     *
     * @return bool
     */
    protected function callEndpoint(array $arguments): bool
    {
        if (!static::$controller->_hasPermission()) {
            static::$controller->_forbidden();

            return true;
        }

        // Checks if has no arguments of first argument is not an endpoint
        if (!$endpoint = $this->getEndpoint($arguments)) {
            // Injects index as first argument
            array_unshift($arguments, 'index');
            // Checks if index is an endpoint
            $endpoint = $this->getEndpoint($arguments);
        }

        // Returns false if has no callable endpoint
        if (!$endpoint) {
            return false;
        }

        // Removes the fist argument
        array_shift($arguments);

        // Call the endpoint method and passes the rest of arguments
        static::$controller->$endpoint($arguments);

        return true;
    }

    /**
     * Tries to discover a web controller from the URI segments.
     *
     * @return bool
     */
    protected function discoverController(): bool
    {
        // if (self::$envType === self::ENV_TYPE_CLI) {
        //     return false;
        // }

        $uri = URI::getInstance();

        $this->setAuthDriver();

        if (Request::getInstance()->isHead() && $uri->host() == '') {
            $response = Response::getInstance();
            $response->header()->pragma('no-cache');
            $response->header()->expires('0');
            $response->header()->cacheControl('must-revalidate, post-check=0, pre-check=0');
            $response->header()->cacheControl('private', false);

            return true;
        }

        // Updates the configuration host
        self::$configuration->configHost($uri->host());

        $segments = $uri->getSegments();
        $segment = $this->findController('App\\Controllers\\Web\\', $segments);
        if ($segment < 0) {
            return $this->discoverMagic();
        }

        // Extracts extra segments as arguments
        $arguments = array_slice($segments, $segment);
        array_splice($segments, $segment);

        return $this->callEndpoint($arguments);
    }

    /**
     * Tries to discover an internal magic endpoint.
     *
     * @return bool
     */
    protected function discoverMagic(): bool
    {
        if (!Request::getInstance()->isGet()) {
            return false;
        }

        $segments = URI::getInstance()->getSegments();

        if (empty(($segments)) || $segments[0] !== 'springy') {
            return false;
        }

        $response = Response::getInstance();

        if (count($segments) == 2 && $segments[1] == 'about') {
            $response->body(Copyright::getInstance()->content());

            return true;
        }

        return false;
    }

    /**
     * Gets the controller endpoint name.
     *
     * @param array $arguments
     *
     * @return string|bool
     */
    protected function getEndpoint(array $arguments)
    {
        // Gets first segment of arguments as endpoint method, if has
        $endpoint = array_shift($arguments);
        if ($endpoint && is_callable([static::$controller, $endpoint])) {
            return $endpoint;
        }

        return false;
    }

    /**
     * Throws a 404 Page Not Found error.
     *
     * @throws Http404Error
     *
     * @return void
     */
    protected function notFound()
    {
        throw new Http404Error();
    }

    /**
     * Sets up authentication driver event handler.
     *
     * @return void
     */
    protected function setupAuthDrv()
    {
        $driver = self::$configuration->get('application.authentication.driver');

        if ($driver === null) {
            app()->bind('user.auth.driver', function ($data) {
                $hasher = $data['user.auth.hasher'];
                $identity = $data['user.auth.identity'];

                return new AuthDriver($hasher, $identity);
            });
        } elseif ($driver instanceof Closure || is_object($driver)) {
            app()->bind('user.auth.driver', $driver);
        }
    }

    /**
     * Instantiates authentication object event handler.
     *
     * @param string $element
     * @param mixed  $default
     *
     * @return void
     */
    protected function setupAuthEvt(string $element, $default = null)
    {
        $option = self::$configuration->get('application.authentication.'.$element, $default);

        if ($option === null) {
            return;
        } elseif ($option instanceof Closure || is_object($option)) {
            app()->bind('user.auth.'.$element, $option);
        } elseif (is_string($option)) {
            app()->bind('user.auth.'.$element, function () use ($option) {
                return new $option();
            });
        }
    }

    /**
     * Starts the authentication driver instance.
     *
     * @return void
     */
    public function setAuthDriver()
    {
        if (is_array(self::$configuration->get('application.authentication'))) {
            $this->setupAuthEvt('hasher', 'Springy\Security\BCryptHasher');
            $this->setupAuthEvt('identity');
            $this->setupAuthDrv();
            app()->instance('user.auth.manager', function ($data) {
                return new Authentication($data['user.auth.driver']);
            });
        }
    }

    /**
     * Sends the application output.
     *
     * @return void
     */
    public function send()
    {
        Response::getInstance()->send(
            self::$configuration->get('application.debug')
        );
    }
}
