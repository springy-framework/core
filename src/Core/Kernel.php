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

/**
 * Framework kernel.
 */
class Kernel
{
    // Framework version
    public const VERSION = '5.0.0';

    // Constants path
    public const PATH_WEB_ROOT = 'ROOT';

    public const PATH_APPLICATION = 'APP';
    public const PATH_VAR = 'VAR';
    public const PATH_ROOT = 'ROOT';

    /** @var static Kernel globally instance */
    protected static $instance;

    /** @var float application started time */
    protected $startime;
    /** @var Handler the application error/exception handler */
    protected $errorHandler;
    /** @var mixed the controller object */
    protected $controller;
    /** @var mixed the hook object */
    protected $hook;

    /// System path
    private static $paths = [];

    /**
     * Constructor.
     *
     * Is not allowed to call from outside to prevent from creating multiple instances.
     */
    protected function __construct($appConf = null)
    {
        $this->errorHandler = new Handler();
        self::$instance = $this;

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
        if ($this->hook === null || !is_callable([$this->hook, 'shutdown'])) {
            return;
        }

        $this->hook->shutdown();
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
     * Tryes to load a full qualified name controller class.
     *
     * @param string $name
     * @param array  $segments
     *
     * @return bool
     */
    protected function loadController(string $name, array $segments): bool
    {
        if (!class_exists($name)) {
            return false;
        }

        // Creates the controller
        $this->controller = new $name($segments);

        return true;
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
        $normalized = [];
        foreach ($segments as $value) {
            $normalized[] = $this->normalizeSegment($value);
        }

        return implode('\\', $normalized);
    }

    /**
     * Normalizes the segment name to StudlyCaps.
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeSegment(string $name): string
    {
        $normalized = [];
        $segments = explode('-', $name);
        foreach ($segments as $value) {
            $normalized[] = $value ? ucwords($value, '_') : '-';
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
     * Returns the application error and exception handler.
     *
     * @return Handler
     */
    public function errorHandler(): Handler
    {
        return $this->errorHandler;
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

    /**
     * Runs the application.
     *
     * @param float $startime
     *
     * @return self
     */
    public function run(float $startime = null): self
    {
        // Can be executed once
        if ($this->startime !== null) {
            return self::$instance;
        }

        // Overwrites the application started time if defined
        $this->startime = $startime ?? microtime(true);

        if (!$this->discoverController()) {
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
        return microtime(true) - $this->startime;
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
            $env = php_sapi_name() === 'cli' ? 'console' : URI::getInstance()->getHost();

            // Verify if has an alias for host
            foreach ($alias as $host => $val) {
                if (preg_match('/^' . $host . '$/', $env)) {
                    $env = $val;
                    break;
                }
            }

            if (empty($env)) {
                $env = 'unknown';
            }
        }

        Configuration::getInstance()->setEnvironment($env);
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

        $configuration = Configuration::getInstance();
        $configuration->setPath($conf['config_path']);
        $configuration->load('main');

        ini_set('date.timezone', $conf['timezone'] ?? 'UTC');
        ini_set('default_charset', $conf['charset'] ?? 'UTF-8');

        $this->setEnvironment(
            $conf['environment'] ?? '',
            $conf['environments'] ?? []
        );

        $this->errorHandler->setLogDir($conf['errors_log'] ?? '');
        $this->errorHandler->setUnreportable($conf['unreportable_errors'] ?? []);
        $this->errorHandler->setWebmasters($conf['errors_reporting'] ?? '');

        // Define the application paths
        // $this->path(self::PATH_WEB_ROOT, $conf['ROOT_PATH']);
        // self::path(self::PATH_APPLICATION, $conf['APP_PATH'] ?? realpath($conf['ROOT_PATH'].'/../app'));

        return self::$instance;
    }

    /**
     * Returns current instance.
     *
     * @return static
     */
    public static function getInstance($appConf = null): self
    {
        if (is_null(self::$instance)) {
            new self($appConf);
        }

        return self::$instance;
    }
}
