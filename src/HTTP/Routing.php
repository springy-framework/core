<?php
/**
 * Routing for the web application requisition.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

use Springy\Core\Configuration;
use Springy\Exceptions\SpringyException;

class Routing
{
    /** @var mixed found handling */
    protected $handling;
    /** @var array parameters */
    protected $params;
    /** @var array the routes */
    protected $routes;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->params = [];
        $this->routes = [
            'GET' => [],
            'POST' => [],
            'PUT' => [],
            'DELETE' => [],
            'OPTIONS' => [],
            'PATCH' => [],
            'HEAD' => [],
        ];

        $config = Configuration::getInstance();

        $routes = $config->get('routing.routes', []);
        foreach ($routes as $pattern => $handling) {
            $this->addPattern($pattern, $handling);
        }
    }

    /**
     * Adds a route pattern the defined request method.
     *
     * @param string $method
     * @param string $pattern
     * @param string $handling
     *
     * @throws SpringyException
     *
     * @return void
     */
    public function addMethod(string $method, string $pattern, $handling)
    {
        if ($method === '*') {
            foreach (array_keys($this->routes) as $method) {
                $this->addMethod($method, $pattern, $handling);
            }

            return;
        }

        if (!isset($this->routes[$method])) {
            throw new SpringyException(sprintf('Invalid method %s.', $method));
        }

        if (is_array($handling)) {
            foreach ($handling as $subPattern => $realHandling) {
                $this->addMethod($method, $pattern.$subPattern, $realHandling);
            }

            return;
        }

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handling' => $handling,
        ];
    }

    /**
     * Adds a pattern to the list of routes.
     *
     * @param string $pattern
     * @param string $handling
     *
     * @throws SpringyException
     *
     * @return void
     */
    public function addPattern(string $pattern, $handling)
    {
        $parts = explode(':', $pattern);
        if (count($parts) < 2) {
            throw new SpringyException('Invalid route pattern.');
        }

        foreach (explode('|', $parts[0]) as $method) {
            $this->addMethod($method, $parts[1], $handling);
        }
    }

    /**
     * Gets the controller method to be invoked.
     *
     * @return string
     */
    public function getMethod(): string
    {
        if (null === $this->handling) {
            return '';
        }

        $parts = explode('@', $this->handling);

        return $parts[1] ?? 'index';
    }

    /**
     * Gets de controller full qualified namespace.
     *
     * @return string
     */
    public function getName(): string
    {
        if (null === $this->handling) {
            return '';
        }

        return explode('@', $this->handling)[0];
    }

    /**
     * Gets the array of parameters.
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Checks whether a route was found.
     *
     * @return bool
     */
    public function hasFound(): bool
    {
        return null !== $this->handling;
    }

    /**
     * Parses current URI in defined routes.
     *
     * @return string|null
     */
    public function parse()
    {
        $method = Request::getInstance()->getMethod();
        $uri = URI::getInstance()->getURIString();

        foreach ($this->routes[$method] as $route) {
            $route['pattern'] = preg_replace('/\/{(.*?)}/', '/(.*?)', $route['pattern']);

            if (preg_match_all('#^'.$route['pattern'].'$#', $uri, $matches, PREG_OFFSET_CAPTURE)) {
                // Index 0 does not matter to us
                $matches = array_slice($matches, 1);

                // Extracts only the parameters from the matched URL parameters
                $this->params = array_map(function ($match, $index) use ($matches) {
                    // The is following parameters?
                    if (isset($matches[$index + 1])
                        && isset($matches[$index + 1][0])
                        && is_array($matches[$index + 1][0])) {
                        // Take the substring from the current param position until the next one's position
                        return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                    }

                    return isset($match[0][0]) ? trim($match[0][0], '/') : null;
                }, $matches, array_keys($matches));

                $this->handling = $route['handling'];

                return $this->handling;
            }
        }
    }
}
