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

use Springy\HTTP\URI;
use Springy\Exceptions\Handler;

class Kernel
{
    // Framework version
    const VERSION = '5.0.0';

    // Path constants
    const PATH_PROJECT = 'PROJ';
    const PATH_CONF = 'CONF';
    const PATH_APPLICATION = 'APP';
    const PATH_VAR = 'VAR';
    const PATH_CLASSES = 'CLASSES';
    const PATH_CONTROLLER = 'CONTROLLER';
    const PATH_LIBRARY = 'LIB';
    const PATH_ROOT = 'ROOT';
    const PATH_WEB_ROOT = 'ROOT';
    const PATH_VENDOR = 'VENDOR';
    const PATH_MIGRATION = 'MIGRATION';
    // Path constants to back compatibility
    const PATH_CONFIGURATION = self::PATH_CONF;
    const PATH_SYSTEM = self::PATH_APPLICATION;
    const PATH_CLASS = self::PATH_CLASSES;

    /** @var self Kernel globally instance */
    protected static $instance;

    /** @var string System name */
    protected static $name = '';
    /** @var array System version */
    protected static $version = [0, 0, 0];
    /** @var string Project code name */
    protected static $projName = '';
    /** @var string System environment */
    protected static $environment = '';
    /** @var string System charset */
    protected static $charset = 'UTF-8';

    /** @var float Application started time */
    protected static $startime;
    /** @var Handler Application error/exception handler */
    protected static $errorHandler;

    /// Determina o root de controladoras
    private static $controller_root = [];
    /// Caminho do namespace do controller
    private static $controller_namespace = null;
    /// The controller file path name
    private static $controllerFile = null;
    /// The controller file class name
    private static $controllerName = null;
    /// Run global pre-controller switch
    private static $runGlobal = true;

    /// System path
    private static $paths = [];

    /// List of error hook functions
    private static $errorHooks = [];

    /// Default template vars
    private static $templateVars = [];
    /// Default template functions
    private static $templateFuncs = [];

    /**
     * The system charset.
     *
     * @param string $charset if defined, set the system charset.
     *
     * @return string A string containing the system charset.
     */
    public function charset(string $charset = null): string
    {
        if ($charset !== null) {
            self::$charset = $charset;
            ini_set('default_charset', $charset);
        }

        return self::$charset;
    }

    /**
     * Configures the application.
     *
     * @param array $conf
     *
     * @return self
     */
    public function config(array $conf): self
    {
        $instance = self::getInstance();

        ini_set('date.timezone', $conf['TIMEZONE'] ?? 'UTC');

        $instance->charset($conf['CHARSET'] ?? 'UTF-8');
        $instance->systemName($conf['SYSTEM_NAME'] ?? 'Application');
        $instance->systemVersion($conf['SYSTEM_VERSION']) ?? [1, 0, 0];
        $instance->projectCodeName($conf['PROJECT_CODE_NAME'] ?? '');
        $instance->environment(
            $conf['ENVIRONMENT'] ?? '',
            $conf['ENVIRONMENT_ALIAS'] ?? [],
            $conf['ENVIRONMENT_VARIABLE'] ?? 'ENVIRONMENT'
        );

        // Check basic configuration path
        if (!isset($conf['ROOT_PATH'])) {
            throw new \Exception('Document root configuration not found.', E_USER_ERROR);
        }

        // Define the application paths
        $instance->path(self::PATH_WEB_ROOT, $conf['ROOT_PATH']);
        // self::path(self::PATH_APPLICATION, $conf['APP_PATH'] ?? realpath($conf['ROOT_PATH'].'/../app'));

        return $instance;
    }

    /**
     * The system environment.
     *
     * @param string $env   if defined, set the system environment.
     * @param array  $alias
     * @param string $envar
     *
     * @return A string containing the system environment
     */
    public function environment(string $env = null, array $alias = [], string $envar = ''): string
    {
        if ($env !== null) {
            // Define environment by host?
            if (trim($env) === '') {
                if (trim($envar) !== '') {
                    $env = getenv($envar);
                }

                $env = empty($env) ? (
                    (php_sapi_name() === 'cli') ? 'cli' : URI::getInstance()->host()
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

            self::$environment = $env;
        }

        return self::$environment;
    }

    /**
     * Returns the application error and exception handler.
     *
     * @return Handler
     */
    public function errorHandler()
    {
        return self::$errorHandler;
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
     * The project code name.
     *
     * @param string $name - if defined, set the project code name.
     *
     * @return string A string containing the project code name.
     *
     * @see https://en.wikipedia.org/wiki/Code_name#Project_code_name
     */
    public function projectCodeName(string $name = null): string
    {
        if ($name !== null) {
            self::$projName = $name;
        }

        return self::$projName;
    }

    public function run(float $startime = null)
    {
        // Can be executed once
        if (self::$startime !== null) {
            return;
        }

        self::$startime = $startime ?? microtime(true);
        self::$errorHandler = new Handler();


        return self::getInstance();
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
     * The system name.
     *
     * @param string $name if defined, set the system name.
     *
     * @return string A string containing the system name.
     */
    public function systemName(string $name = null): string
    {
        if ($name !== null) {
            self::$name = $name;
        }

        return self::$name;
    }

    /**
     * The system version.
     *
     * @param mixed $major if defined, set the major part of the system version. Can be an array with all parts.
     * @param mixed $minor if defined, set the minor part of the system version.
     * @param mixed $build if defined, set the build part of the system version.
     *
     * @return string A string containing the system version.
     */
    public function systemVersion($major = null, $minor = null, $build = null): string
    {
        if (is_array($major) && is_null($minor) && is_null($build)) {
            return self::getInstance()->systemVersion(
                $major[0] ?? 1,
                $major[1] ?? 0,
                $major[2] ?? 0);
        }

        if (!is_null($major) && !is_null($minor) && !is_null($build)) {
            self::$version = [$major, $minor, $build];
        } elseif (!is_null($major) && !is_null($minor)) {
            self::$version = [$major, $minor];
        } elseif (!is_null($major)) {
            self::$version = [$major];
        }

        return is_array(self::$version) ? implode('.', self::$version) : self::$version;
    }

    /**
     * Returns current instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
