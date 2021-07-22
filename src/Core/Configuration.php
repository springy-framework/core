<?php

/**
 * Application configuration handler.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Allan Marques <allan.marques@ymail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   4.0.0
 */

namespace Springy\Core;

use Springy\Exceptions\SpringyException;
use Springy\Utils\ArrayUtils;

/**
 * Application configuration handler.
 */
class Configuration
{
    /** @var self globally singleton instance */
    protected static $instance;

    /** @var array the array of configuration */
    protected $confs;
    /** @var string the configuration root path */
    protected $configPath;
    /** @var array the configuration sets */
    protected $configSets;
    /** @var string the configuration environment path */
    protected $envDir;
    /** @var string the host for configurations overwrite */
    protected $host;

    public const LC_DB = 'db';
    public const LC_MAIL = 'mail';
    public const LC_SYSTEM = 'system';
    public const LC_TEMPLATE = 'template';
    public const LC_URI = 'uri';

    /**
     * Constructor.
     */
    final private function __construct(string $path = null, string $env = null, string $host = null)
    {
        $this->configSets = [];
        $this->configPath = $path ?? __DIR__ . '/../../../../conf';
        $this->envDir = $env ?? 'production';
        $this->host = $host ?? '';
        self::$instance = $this;
    }

    /**
     * Prevents the instance from being cloned (which would create a second instance of it).
     */
    private function __clone()
    {
    }

    /**
     * Prevents from being unserialized (which would create a second instance of it).
     *
     * @SuppressWarnings(UnusedPrivateMethod)
     */
    private function __wakeup()
    {
    }

    /**
     * Gets the settings name from dotted entry string.
     *
     * @param string $entry
     *
     * @return string
     */
    protected function getSettingsName(string $entry): string
    {
        if (preg_match('/[\/?*:;{}\\\|"\'\[\]<>]+/', $entry)) {
            throw new SpringyException('Configuration key invalid. Space found.');
        }

        $set = explode('.', $entry)[0];

        if ($set == '') {
            throw new SpringyException('Configuration key invalid. Empty configuration set.');
        }

        return $set;
    }

    /**
     * Loads the configuration from a Json file.
     *
     * @param string $file configuration file name.
     * @param string $set  name of the configuration set.
     *
     * @return void
     */
    private function loadJson($file, $set)
    {
        if (!file_exists($file . '.json')) {
            return;
        }

        // Initializes the config set if needed
        $this->prepareSetting($set);

        $str = file_get_contents($file . '.json');

        if (!$str) {
            throw new SpringyException('Can not open the configuration file ' . $file . '.json');
        }

        $conf = json_decode($str, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new SpringyException('Parse error at ' . $file . '.json: ' . json_last_error_msg());
        }

        $this->configSets[$set] = array_replace_recursive($this->configSets[$set], $conf);
    }

    /**
     * Loads the configuration file in PHP script format.
     *
     * @param string $file configuration file name.
     * @param string $set  name of the configuration set.
     *
     * @return void
     */
    protected function loadScript($file, $set)
    {
        if (!file_exists($file . '.php')) {
            return;
        }

        // Initializes the config set if needed
        $this->prepareSetting($set);

        $conf = require $file . '.php';
        $this->configSets[$set] = array_replace_recursive($this->configSets[$set], $conf);
    }

    /**
     * Prepares the configuration settings array if needed.
     *
     * @param string $set
     *
     * @return void
     */
    protected function prepareSetting(string $set)
    {
        if (isset($this->configSets[$set])) {
            return;
        }

        $this->configSets[$set] = [];
    }

    /**
     * Get or set the configurations host.
     *
     * @param string $host
     *
     * @return string
     */
    public function configHost(string $host = null): string
    {
        if ($host !== null) {
            $this->host = $host;
        }

        return $this->host;
    }

    /**
     * Gets the configuration entry.
     *
     * @param string $entry   dotted string of the configuration entry.
     * @param mixed  $default default value.
     *
     * @return mixed
     */
    public function get(string $entry, $default = null)
    {
        $set = $this->getSettingsName($entry);

        if (!isset($this->configSets[$set])) {
            $this->load($set);
        }

        return ArrayUtils::newInstance()->dottedGet($this->configSets, $entry, $default);
    }

    /**
     * Gets the environment folder.
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->envDir;
    }

    /**
     * Gets the configurations file path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->configPath;
    }

    /**
     * Load the configuration file for given set.
     *
     * @param string $set
     *
     * @return void
     */
    public function load(string $set)
    {
        unset($this->configSets[$set]);

        // Load configuration file from main folder
        $this->loadScript($this->configPath . DS . $set, $set);
        $this->loadScript($this->configPath . DS . $set . '-' . $this->host, $set);
        $this->loadJson($this->configPath . DS . $set, $set);
        $this->loadJson($this->configPath . DS . $set . '-' . $this->host, $set);

        // Load configuration file from environment folder
        if ($this->envDir) {
            $this->loadScript($this->configPath . DS . $this->envDir . DS . $set, $set);
            $this->loadScript($this->configPath . DS . $this->envDir . DS . $set . '-' . $this->host, $set);
            $this->loadJson($this->configPath . DS . $this->envDir . DS . $set, $set);
            $this->loadJson($this->configPath . DS . $this->envDir . DS . $set . '-' . $this->host, $set);
        }

        // Check if configuration was loaded
        if (!isset($this->configSets[$set])) {
            throw new SpringyException(
                'Configuration settings "'
                . $set
                . '" not found in the environment "'
                . $this->envDir
                . '".'
            );
        }
    }

    /**
     * Saves the configuration settings to a JSON file.
     *
     * @param string $set name of the configuration set.
     *
     * @return void
     */
    public function save(string $set)
    {
        $fileName = $this->configPath . DS . ($this->envDir ? $this->envDir . DS : '') . $set . '.json';

        if (!file_put_contents($fileName, json_encode($this->configSets[$set], JSON_PRETTY_PRINT))) {
            throw new SpringyException('Can not write to ' . $fileName);
        }
    }

    /**
     * Set the values for a configuration key.
     *
     * This change is temporary and will exist only during application execution.
     * No changes will be made to the configuration files.
     *
     * @param string $entry dotted string of the configuration key.
     * @param mixed  $value new value for configuration.
     *
     * @return void
     */
    public function set(string $entry, $value)
    {
        $set = $this->getSettingsName($entry);

        // Initializes the config set if needed
        $this->prepareSetting($set);

        ArrayUtils::newInstance()->dottedSet($this->configSets, $entry, $value);
    }

    /**
     * Sets the environment folder.
     *
     * @param string $env
     *
     * @return void
     */
    public function setEnvironment(string $env)
    {
        $this->envDir = $env;
    }

    /**
     * Sets the configurations file path.
     *
     * @param string $path
     *
     * @return void
     */
    public function setPath(string $path = null)
    {
        $this->configPath = $path;
    }

    /**
     * Returns current instance.
     *
     * @return static
     */
    public static function getInstance(string $path = null, string $env = null, string $host = null): self
    {
        if (is_null(static::$instance)) {
            new static($path, $env, $host);
        }

        if (null !== $path) {
            static::$instance->setPath($path);
        }
        if (null !== $env) {
            static::$instance->setEnvironment($env);
        }
        if (null !== $host) {
            static::$instance->configHost($host);
        }

        return static::$instance;
    }
}
