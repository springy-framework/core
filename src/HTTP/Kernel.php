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

use Springy\Core\Configuration;
use Springy\Core\Copyright;
use Springy\Core\Kernel as MainKernel;
use Springy\Exceptions\Http404Error;
use Springy\Security\AuthDriver;
use Springy\Security\Authentication;

class Kernel extends MainKernel
{
    /** @var static Kernel globally instance */
    protected static $instance;

    protected $endpoint;
    protected $params;

    /**
     * Checks if endpoint exists.
     *
     * @param string $endpoint
     *
     * @return string
     */
    protected function checkEndpoint(string $endpoint): string
    {
        if (is_callable([$this->controller, $endpoint])) {
            return $endpoint;
        }

        return 'index';
    }

    /**
     * Tries to discover a web controller from the URI segments.
     *
     * @return bool
     */
    protected function discoverController(): bool
    {
        $uri = URI::getInstance();

        $this->setAuthDriver();

        if (Request::getInstance()->isHead() && $uri->getHost() == '') {
            $response = Response::getInstance();
            $response->header()->pragma('no-cache');
            $response->header()->expires('0');
            $response->header()->cacheControl('must-revalidate, post-check=0, pre-check=0');
            $response->header()->cacheControl('private', false);

            return true;
        }

        // Updates the configuration host
        Configuration::getInstance()->configHost($uri->getHost());

        if (!$this->hasController($uri->getSegments())) {
            return $this->discoverMagic();
        } elseif (!is_callable([$this->controller, $this->endpoint])) {
            return false;
        } elseif (!$this->controller->_hasPermission()) {
            $this->controller->_forbidden();

            return true;
        }

        call_user_func([$this->controller, $this->endpoint], $this->params);

        return true;
    }

    /**
     * Tries to discover an internal magic endpoint.
     *
     * @return bool
     */
    protected function discoverMagic(): bool
    {
        if (!Request::getInstance()->isGet() && !Request::getInstance()->isPost()) {
            return false;
        }

        $segments = URI::getInstance()->getSegments();

        if (count($segments) < 2 || count($segments) > 2 || $segments[0] !== 'springy') {
            return false;
        }

        array_shift($segments);
        $response = Response::getInstance();

        if (Request::getInstance()->isGet() && $segments[0] == 'about') {
            $copyright = new Copyright();
            $response->body($copyright->content());

            return true;
        } elseif ($segments[0] == 'terminal') {
            $this->controller = new Terminal($segments);

            return true;
        }

        return false;
    }

    /**
     * Finds the controller.
     *
     * @param array $segments
     *
     * @return bool
     */
    protected function hasController(array $segments): bool
    {
        $routing = new Routing();
        $routing->parse();
        if ($routing->hasFound()) {
            $this->endpoint = $routing->getMethod();
            $this->params = $routing->getParams();

            return $this->loadController($routing->getName(), $segments);
        }

        $this->params = [];
        $arguments = $segments;
        $namespace = $this->getNamespace($arguments);
        $endpoint = 'index';
        do {
            // Adds and finds an Index controller in current $arguments path
            $arguments[] = 'Index';
            if ($this->loadController($namespace.$this->normalizeNamePath($arguments), $segments)) {
                $this->endpoint = $this->checkEndpoint($endpoint);

                return true;
            }

            // Removes Index and finds the full qualified name controller
            array_pop($arguments);
            if (count($arguments)
                && $this->loadController($namespace.$this->normalizeNamePath($arguments), $segments)) {
                $this->endpoint = $this->checkEndpoint($endpoint);

                return true;
            }

            $endpoint = array_pop($arguments);
            array_unshift($this->params, $endpoint);
        } while (count($arguments));

        return false;
    }

    /**
     * Gets the controller namespace.
     *
     * @param array $segments
     *
     * @return string
     */
    protected function getNamespace(array &$segments): string
    {
        $config = Configuration::getInstance();
        $uri = '/'.implode('/', $segments);
        foreach ($config->get('routing.namespaces', []) as $route => $namespace) {
            $pattern = sprintf('#^%s(/(.+))?$#', $route);
            if (preg_match_all($pattern, $uri, $matches, PREG_PATTERN_ORDER)) {
                $segments = explode('/', trim($matches[1][0], '/'));

                return $namespace.'\\';
            }
        }

        $host = current_host();
        foreach ($config->get('routing.hostings', []) as $route => $namespace) {
            $pattern = sprintf('#^%s$#', $route);
            if (preg_match_all($pattern, $host, $matches, PREG_PATTERN_ORDER)) {
                return $namespace.'\\';
            }
        }

        return $config->get('routing.namespace', 'App\\Controllers\\Web\\');
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
        $driver = Configuration::getInstance()->get('application.authentication.driver');

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
        $option = Configuration::getInstance()->get('application.authentication.'.$element, $default);

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
        if (is_array(Configuration::getInstance()->get('application.authentication'))) {
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
        Response::getInstance()->send();
    }
}
