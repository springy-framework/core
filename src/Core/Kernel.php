<?php
/**
 * Framework kernel.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Lucas Cardozo <lucas.cardozo@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   3.0.0
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

namespace Springy\Core;

use Springy\Exceptions\Handler;
use Springy\Exceptions\Http404Error;
use Springy\Exceptions\SpringyException;
use Springy\HTTP\Request;
use Springy\HTTP\Response;
use Springy\HTTP\URI;
use Springy\Security\AuthDriver;
use Springy\Security\Authentication;

class Kernel
{
    // Framework version
    const VERSION = '5.0.0';

    // Execution environment type constants
    const ENV_TYPE_CLI = 'cli';
    const ENV_TYPE_WEB = 'web';

    // Constants path
    const PATH_WEB_ROOT = 'ROOT';

    const PATH_PROJECT = 'PROJ';
    const PATH_CONF = 'CONF';
    const PATH_APPLICATION = 'APP';
    const PATH_VAR = 'VAR';
    const PATH_CLASSES = 'CLASSES';
    const PATH_CONTROLLER = 'CONTROLLER';
    const PATH_LIBRARY = 'LIB';
    const PATH_ROOT = 'ROOT';
    const PATH_VENDOR = 'VENDOR';
    const PATH_MIGRATION = 'MIGRATION';
    // Path constants to back compatibility
    const PATH_SYSTEM = self::PATH_APPLICATION;
    const PATH_CLASS = self::PATH_CLASSES;

    /** @var self Kernel globally instance */
    protected static $instance;

    /** @var float application started time */
    protected static $startime;
    /** @var string the execution environment type */
    protected static $envType;
    /** @var Configuration the configuration handler */
    protected static $configuration;
    /** @var Handler the application error/exception handler */
    protected static $errorHandler;
    /** @var mixed the controller object */
    protected static $controller;
    /** @var mixed the hook object */
    protected static $hook;

    /// System path
    private static $paths = [];

    /**
     * Constructor.
     */
    public function __construct($appConf = null)
    {
        if (self::$instance !== null) {
            return;
        }

        self::$envType = (php_sapi_name() === 'cli') ? self::ENV_TYPE_CLI : self::ENV_TYPE_WEB;
        self::$configuration = new Configuration();
        self::$errorHandler = new Handler();
        self::$instance = $this;

        if ($appConf !== null) {
            $this->setUp($appConf);
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if (self::$hook === null || !is_callable([self::$hook, 'shutdown'])) {
            return;
        }

        self::$hook->shutdown();
    }

    /**
     * Calls the controller endpoint.
     *
     * @param array $arguments
     *
     * @return bool
     */
    protected function callEndpoint(array $arguments): bool
    {
        if (!self::$controller->_hasPermission()) {
            self::$controller->_forbidden();

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
        self::$controller->$endpoint($arguments);

        return true;
    }

    /**
     * Tries to discover a command line controller.
     *
     * @return void
     */
    protected function discoverCliController()
    {
        if (self::$envType === self::ENV_TYPE_WEB) {
            return false;
        }

        $segment = $this->findController('App\\Controllers\\Cli\\', []);
        if ($segment < 0) {
            return false;
        }

        return $this->callEndpoint([]);
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
     * Tries to discover a web controller from the URI segments.
     *
     * @return bool
     */
    protected function discoverWebController(): bool
    {
        if (self::$envType === self::ENV_TYPE_CLI) {
            return false;
        }

        $uri = URI::getInstance();

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
            return false;
        }

        // Extracts extra segments as arguments
        $arguments = array_slice($segments, $segment);
        array_splice($segments, $segment);

        return $this->callEndpoint($arguments);
    }

    /**
     * Finds the controller.
     *
     * @param string $baseNS
     * @param array  $segments
     *
     * @return int
     */
    protected function findController(string $baseNS, array $segments): int
    {
        $arguments = [];

        do {
            $elements = count($segments);

            // Finds an Index controller
            $index = $segments;
            $index[] = 'Index';
            if ($this->loadController($baseNS, $index, $arguments)) {
                return $elements;
            }

            // Finds the full qualified name controller
            if ($elements && $this->loadController($baseNS, $segments, $arguments)) {
                return $elements;
            }

            // Moves the last segment to the arguments array
            // and pop it from the segments array
            array_unshift($arguments, array_pop($segments));
        } while (count($segments));

        return -1;
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
        if ($endpoint && is_callable([self::$controller, $endpoint])) {
            return $endpoint;
        }

        return false;
    }

    /**
     * Tryes to load a full qualified name controller class.
     *
     * @param string $baseNS
     * @param array  $path
     * @param array  $arguments
     *
     * @return bool
     */
    protected function loadController(string $baseNS, array $path, array $arguments): bool
    {
        $name = $baseNS.$this->normalizeNamePath($path);
        if (!class_exists($name)) {
            return false;
        }

        // Loads the hook controller if exists
        $this->loadHookController($baseNS, $arguments);

        // Creates the controller
        self::$controller = new $name($arguments);

        return true;
    }

    /**
     * Loads the application hook controller.
     *
     * @param string $baseNS
     * @param array  $arguments
     *
     * @return void
     */
    protected function loadHookController(string $baseNS, array $arguments)
    {
        $hook = array_slice(explode('\\', trim($baseNS, '\\')), 0, 2);
        $hook[] = 'Hook';
        $name = $this->normalizeNamePath($hook);
        if (!class_exists($name)) {
            return;
        }

        self::$hook = new $name($baseNS, $arguments);
        if (is_callable([self::$hook, 'startup'])) {
            self::$hook->startup();
        }
    }

    /**
     * Normalizes the array of segments to a class namespace.
     *
     * @param array $segments
     *
     * @return string
     */
    protected function normalizeNamePath(array $segments): string
    {
        $count = count($segments) - 1;
        $normalized = [];
        foreach ($segments as $index => $value) {
            $normalized[] = $this->normalizeSegment($value, $index == $count);
        }

        return implode('\\', $normalized);
    }

    /**
     * Normalizes the segment name to StudlyCaps.
     *
     * @param string $name
     * @param bool   $last
     *
     * @return string
     */
    protected function normalizeSegment(string $name, bool $last): string
    {
        $normalized = [];
        $segments = explode('-', $name);
        foreach ($segments as $value) {
            if ($last) {
                $normalized[] = $value ? ucwords($value, '_') : '-';

                continue;
            }

            $normalized[] = $value ?? '-';
        }

        return implode('', $normalized);
    }

    protected function notFound()
    {
        if (self::$envType == self::ENV_TYPE_WEB) {
            throw new Http404Error();
        }
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
     * Returns the configuration handler.
     *
     * @return Configuration
     */
    public function configuration(): Configuration
    {
        return self::$configuration;
    }

    public function controller()
    {
        return self::$controller;
    }

    /**
     * Returns the application error and exception handler.
     *
     * @return Handler
     */
    public function errorHandler(): Handler
    {
        return self::$errorHandler;
    }

    /**
     * The project code name.
     *
     * @return string A string containing the project code name.
     *
     * @see https://en.wikipedia.org/wiki/Code_name#Project_code_name
     */
    public function getAppCodeName(): string
    {
        return config_get('main.app.code_name');
    }

    /**
     * The application name.
     *
     * @return string
     */
    public function getApplicationName(): string
    {
        return config_get('main.app.name', '');
    }

    /**
     * The application version.
     *
     * @return string
     */
    public function getApplicationVersion(): string
    {
        $appVersion = config_get('main.app.version', [1, 0, 0]);

        return is_array($appVersion)
            ? implode('.', $appVersion)
            : $appVersion;
    }

    /**
     * The system environment.
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return self::$configuration->getEnvironment();
    }

    /**
     * Returns the application type.
     *
     * @return string
     */
    public function getEnvironmentType(): string
    {
        return self::$envType;
    }

    /**
     * Returns the application HTTP request instance.
     *
     * @return Request
     */
    public function httpRequest(): Request
    {
        return Request::getInstance();
    }

    /**
     * A path of the system.
     *
     * @param string $component the component constant.
     * @param string $path      if defined, change the path of the component.
     *
     * @return string A string containing the path of the component.
     */
    public function path(string $component, string $path = null): string
    {
        if ($path !== null) {
            self::$paths[$component] = $path;
        }

        return self::$paths[$component] ?? '';
    }

    public function run(float $startime = null): self
    {
        // Can be executed once
        if (self::$startime !== null) {
            return self::$instance;
        }

        // Overwrites the application started time if defined
        self::$startime = $startime ?? microtime(true);

        if (!$this->discoverCliController() && !$this->discoverWebController() && !$this->discoverMagic()) {
            $this->notFound();
        }

        return self::$instance;
    }

    /**
     * Returns the system runtime until now.
     *
     * @return float
     */
    public function runTime(): float
    {
        return microtime(true) - self::$startime;
    }

    /**
     * Sends the application output.
     *
     * @return void
     */
    public function send()
    {
        if (self::$envType === self::ENV_TYPE_WEB) {
            Response::getInstance()->send(
                self::$configuration->get('application.debug')
            );

            return;
        }
    }

    /**
     * Sets the system charset.
     *
     * @param string $charset
     *
     * @return void
     */
    public function setCharset(string $charset)
    {
        config_set('main.charset', $charset);
        ini_set('default_charset', $charset);
    }

    /**
     * Sets the system environment.
     *
     * @param string $env
     * @param array  $alias
     * @param string $envar
     *
     * @return void
     */
    public function setEnvironment(string $env, array $alias = [], string $envar = '')
    {
        // Define environment by host?
        if (trim($env) === '') {
            if (trim($envar) !== '') {
                $env = getenv($envar);
            }

            $env = empty($env) ? (
                (self::$envType === self::ENV_TYPE_CLI) ? 'cli' : URI::getInstance()->host()
            ) : $env;

            // Verify if has an alias for host
            foreach ($alias as $host => $val) {
                if (preg_match('/^'.$host.'$/', $env)) {
                    $env = $val;
                    break;
                }
            }

            if (empty($env)) {
                $env = 'unknown';
            }
        }

        self::$configuration->setEnvironment($env);
    }

    /**
     * Configures the application.
     *
     * @param array|string $conf the array of configuration or
     *                           the full path name of the configuration file.
     *
     * @return self
     */
    public function setUp($conf): self
    {
        if (!is_array($conf) && !is_string($conf)) {
            throw new SpringyException('Invalid application configuration set.');
        } elseif (is_string($conf)) {
            $conf = require $conf;
        }

        // Check basic configuration path
        if (!isset($conf['config_path'])) {
            throw new SpringyException('Configuration files path not found.');
        }

        self::$configuration->configPath($conf['config_path']);
        self::$configuration->load('main');

        ini_set('date.timezone', $conf['timezone'] ?? 'UTC');

        $this->setCharset($conf['charset'] ?? 'UTF-8');
        $this->setEnvironment(
            $conf['environment'] ?? '',
            $conf['ENVIRONMENT_ALIAS'] ?? [],
            $conf['ENVIRONMENT_VARIABLE'] ?? 'ENVIRONMENT'
        );

        if (is_array(self::$configuration->get('application.authentication'))) {
            $this->setupAuthEvt('hasher', 'Springy\Security\BCryptHasher');
            $this->setupAuthEvt('identity');
            $this->setupAuthDrv();
            app()->instance('user.auth.manager', function ($data) {
                return new Authentication($data['user.auth.driver']);
            });
        }

        // Define the application paths
        // $this->path(self::PATH_WEB_ROOT, $conf['ROOT_PATH']);
        // self::path(self::PATH_APPLICATION, $conf['APP_PATH'] ?? realpath($conf['ROOT_PATH'].'/../app'));

        return self::$instance;
    }

    /**
     * Returns current instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            new self();
        }

        return self::$instance;
    }
}
