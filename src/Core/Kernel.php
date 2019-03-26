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
 */

namespace Springy\Core;

use Springy\Exceptions\Handler;
use Springy\Exceptions\SpringyException;
use Springy\HTTP\Request;
use Springy\HTTP\URI;

class Kernel
{
    // Framework version
    const VERSION = '5.0.0';

    // Constants path
    const PATH_WEB_ROOT = 'ROOT';

    const PATH_APPLICATION = 'APP';
    const PATH_VAR = 'VAR';
    const PATH_ROOT = 'ROOT';

    /** @var static Kernel globally instance */
    protected static $instance;

    /** @var float application started time */
    protected static $startime;
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
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     */
    private function __construct($appConf = null)
    {
        self::$configuration = new Configuration();
        self::$errorHandler = new Handler();
        static::$instance = $this;

        if ($appConf !== null) {
            $this->setUp($appConf);
        }
    }

    /**
     * Prevents the instance from being cloned (which would create a second instance of it).
     */
    private function __clone()
    {
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if (static::$hook === null || !is_callable([static::$hook, 'shutdown'])) {
            return;
        }

        static::$hook->shutdown();
    }

    /**
     * Tries to discover a controller.
     *
     * @return bool
     */
    protected function discoverController(): bool
    {
        return false;
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
        static::$controller = new $name($arguments);

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

        static::$hook = new $name($baseNS, $arguments);
        if (is_callable([static::$hook, 'startup'])) {
            static::$hook->startup();
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

    /**
     * Throws an error.
     *
     * @throws SpringyException
     *
     * @return void
     */
    protected function notFound()
    {
        throw new SpringyException('No controller found.');
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
            return static::$instance;
        }

        // Overwrites the application started time if defined
        self::$startime = $startime ?? microtime(true);

        if (!$this->discoverController()) {
            $this->notFound();
        }

        return static::$instance;
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
     *
     * @return void
     */
    public function setEnvironment(string $env, array $alias = [])
    {
        // Define environment by host?
        if (trim($env) === '') {
            $env = php_sapi_name() === 'cli' ? 'console' : URI::getInstance()->host();

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

        self::$configuration->setPath($conf['config_path']);
        self::$configuration->load('main');

        ini_set('date.timezone', $conf['timezone'] ?? 'UTC');

        $this->setCharset($conf['charset'] ?? 'UTF-8');
        $this->setEnvironment(
            $conf['environment'] ?? '',
            $conf['ENVIRONMENT_ALIAS'] ?? []
        );

        self::$errorHandler->setLogDir($conf['errors_log'] ?? '');
        self::$errorHandler->setUnreportable($conf['unreportable_errors'] ?? []);
        self::$errorHandler->setWebmasters($conf['errors_reporting'] ?? '');

        // Define the application paths
        // $this->path(self::PATH_WEB_ROOT, $conf['ROOT_PATH']);
        // self::path(self::PATH_APPLICATION, $conf['APP_PATH'] ?? realpath($conf['ROOT_PATH'].'/../app'));

        return static::$instance;
    }

    /**
     * Returns current instance.
     *
     * @return static
     */
    public static function getInstance($appConf = null): self
    {
        if (static::$instance === null) {
            new static($appConf);
        }

        return static::$instance;
    }
}
