<?php
/**
 * DMBS connector for MySQL servers.
 */

namespace Springy\Database\Connectors;

use Exception;
use PDO;
use Springy\Exceptions\SpringyException;

class MySQL
{
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];
    protected $pdo;
    protected $host;
    protected $socket;
    protected $port;
    protected $database;
    protected $username;
    protected $password;
    protected $retries;
    protected $retrySleep;
    protected $servers = [];

    public function __construct(array $config, string $charset = 'UTF8')
    {
        $this->options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \''.$charset.'\'',
            PDO::ATTR_PERSISTENT => $config['persistent'] ?? false,
        ];

        $this->socket = false;
        $this->setHost($config);
        $this->setDatabase($config);
        $this->port = $config['port'] ?? 3128;
        $this->retries = $conf['retries'] ?? 3;
        $this->retrySleep = $conf['retry_sleep'] ?? 1;
        $this->setUsername($conf['username'] ?? '');
        $this->setPassword($conf['password'] ?? '');
    }

    protected function getDsn(): string
    {
        return 'mysql:'.$this->getHostOrSocket().';dbname='.$this->getDatabase();
    }

    protected function getHostOrSocket(): string
    {
        return $this->socket
            ? 'unix_socket='.$this->socket
            : 'host='.$this->host.';port='.$this->port;
    }

    protected function setHost(array $config)
    {
        if (($config['socket'] ?? false)) {
            return $this->setSocket($config);
        }

        $host = ($config['host'] ?? '');

        if (is_array($host)) {
            return $this->setRoundRobin($config);
        }

        if (!$host) {
            throw new SpringyException('Database host server undefined.');
        }

        $this->host = $host;
    }

    protected function setSocket(array $config)
    {
        $socket = ($config['socket'] ?? '');

        if (is_array($socket)) {
            return $this->setRoundRobin($config);
        }

        if (!$socket) {
            throw new SpringyException('Database socket server undefined.');
        }

        $this->socket = $socket;
    }

    public function connect(): PDO
    {
        do {
            try {
                $this->pdo = PDO($this->getDsn(), $this->username, $this->password, $this->options);
            } catch (Exception $exception) {
                if ($this->retries) {
                    $this->retries -= 1;
                    sleep($this->retrySleep);

                    continue;
                }

                throw $exception;
            }
        } while ($this->pdo === null);

        return $this->pdo;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setDatabase(array $config)
    {
        $database = ($config['database'] ?? '');

        if (!$database) {
            throw new SpringyException('Database name undefined.');
        }

        $this->database = $database;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function setUsername(string $username)
    {
        if (!$username) {
            throw new SpringyException('Database username undefined.');
        }

        $this->username = $username;
    }
}
