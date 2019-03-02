<?php
/**
 * DMBS connector basic implementation.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Connectors;

use Memcached;
use PDO;
use Springy\Exceptions\SpringyException;

class Connector
{
    /** @var string charset configuration */
    protected $charset;
    /** @var string name of the database */
    protected $database;
    /** @var array PDO constructor options */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];
    /** @var string the database user password */
    protected $password;
    /** @var PDO the PDO object */
    protected $pdo;
    /** @var Closure the round robin controller */
    protected $roundRobin;
    /** @var string timezone configuration */
    protected $timezone;
    /** @var string the database username */
    protected $username;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->timezone = $config['timezone'] ?? '';
        $this->setDatabase($config['database'] ?? '');
        $this->setUsername($config['username'] ?? '');
        $this->setPassword($config['password'] ?? '');
        $this->configRoundRobin($config['round_robin'] ?? []);
    }

    /**
     * Configures the round robin controller driver.
     *
     * @param array $config
     *
     * @return void
     */
    protected function configRoundRobin(array $config)
    {
        $driver = $config['driver'] ?? false;
        if ($driver === false) {
            $this->roundRobin = function (array $list) {
                return $list[0];
            };

            return;
        }

        $drivers = [
            'file'      => 'rRobinFile',
            'memcached' => 'rRobinMemcached',
        ];

        if (!isset($drivers[$driver])) {
            throw new SpringyException('Round robin driver not supported.');
        }

        $driver = $drivers[$driver];

        call_user_func([$this, $driver], $config);
    }

    /**
     * Instantiates round robin controller by file driver.
     *
     * @param array $config
     *
     * @return void
     */
    protected function rRobinFile(array $config)
    {
        $file = $config['file'] ?? null;
        if ($file === null) {
            throw new SpringyException('Round robin file undefined');
        }

        if (!file_exists($file)) {
            file_put_contents($file, '-1');
        }

        $this->roundRobin = function (array $list) use ($file) {
            $next = ((int) file_get_contents($file)) + 1;

            if ($next >= count($list)) {
                $next = 0;
            }

            file_put_contents($file, $next);

            return $list[$next];
        };
    }

    /**
     * Instantiates round robin controller by Memcached driver.
     *
     * @param array $config
     *
     * @return void
     */
    protected function rRobinMemcached(array $config)
    {
        $address = $config['address'] ?? null;
        if ($address === null) {
            throw new SpringyException('Round robin Memcached address undefined');
        }

        $port = $config['port'] ?? '11211';
        $key = $config['key'] ?? 'database_round_robin';

        $this->roundRobin = function (array $list) use ($address, $port, $key) {
            $memCached = new Memcached();
            $memCached->addServer($address, $port);

            if (!($next = (int) $memCached->get($key))) {
                $next = -1;
            }

            if (++$next >= count($list)) {
                $next = 0;
            }

            $memCached->set($key, $next, 0);

            return $list[$next];
        };
    }

    /**
     * Executes the round robin controller to get next entry in the list.
     *
     * @param array $list
     *
     * @return mixed
     */
    protected function setRoundRobin(array $list)
    {
        return call_user_func($this->roundRobin, $list);
    }

    /**
     * Connects with the database.
     *
     * @return PDO
     */
    public function connect(): PDO
    {
        do {
            try {
                $this->pdo = new PDO($this->getDsn(), $this->username, $this->password, $this->options);
            } catch (Exception $exception) {
                if ($this->retries) {
                    $this->retries -= 1;
                    sleep($this->retrySleep);

                    continue;
                }

                throw $exception;
            }
        } while ($this->pdo === null);

        if (is_callable([$this, 'afterConnectSettings'])) {
            $this->afterConnectSettings();
        }

        return $this->pdo;
    }

    /**
     * Gets the database name.
     *
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * Returns the database user password.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Returns the PDO object.
     *
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Returns the database username.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Sets the database name.
     *
     * @param string $database
     *
     * @return void
     */
    public function setDatabase(string $database)
    {
        if (!$database) {
            throw new SpringyException('Database name undefined.');
        }

        $this->database = $database;
    }

    /**
     * Sets the database user password.
     *
     * @param string $password
     *
     * @return void
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * Sets the database username.
     *
     * @param string $username
     *
     * @return void
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }
}
