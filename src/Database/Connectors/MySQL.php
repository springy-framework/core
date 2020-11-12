<?php

/**
 * DBMS connector for MySQL servers.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Connectors;

use PDO;
use Springy\Exceptions\SpringyException;

/**
 * DBMS connector for MySQL servers.
 */
class MySQL extends Connector implements ConnectorInterface
{
    /** @var string|array the database host server */
    protected $host;
    /** @var int the database server port */
    protected $port;
    /** @var int connection tentative possible */
    protected $retries;
    /** @var int sleep time in seconds between each try connection */
    protected $retrySleep;
    /** @var string|array the database socket connection */
    protected $socket;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->charset = $config['charset'] ?? 'utf8mb4';
        $this->port = $config['port'] ?? '3306';
        $this->retries = $config['retries'] ?? 3;
        $this->retrySleep = $config['retry_sleep'] ?? 1;
        $this->socket = false;
        $this->setHost($config);

        $this->options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES \'' . $this->charset . '\'';
        $this->options[PDO::ATTR_PERSISTENT] = $config['persistent'] ?? true;
    }

    /**
     * Configures database connection after the connection stablished.
     *
     * @return void
     */
    protected function afterConnectSettings()
    {
        if ($this->timezone) {
            $this->pdo->prepare('SET time_zone="' . $this->timezone . '"')->execute();
        }
    }

    /**
     * Gets string for HOST or SOCKET connector DSN.
     *
     * @return string
     */
    protected function getHostOrSocket(): string
    {
        return $this->socket
            ? 'unix_socket=' . $this->socket
            : 'host=' . $this->host
            . ';port=' . $this->port;
    }

    /**
     * Sets the host property.
     *
     * @param array $config
     *
     * @return void
     */
    protected function setHost(array $config)
    {
        if (($config['socket'] ?? false)) {
            return $this->setSocket($config);
        }

        $host = ($config['host'] ?? '');

        if (is_array($host)) {
            $host = $this->setRoundRobin($host);
        }

        if (!$host) {
            throw new SpringyException('Database host server undefined.');
        }

        $this->host = $host;
    }

    /**
     * Sets the socket property.
     *
     * @param array $config
     *
     * @return void
     */
    protected function setSocket(array $config)
    {
        $socket = ($config['socket'] ?? '');

        if (is_array($socket)) {
            $socket = $this->setRoundRobin($socket);
        }

        if (!$socket) {
            throw new SpringyException('Database socket server undefined.');
        }

        $this->socket = $socket;
    }

    /**
     * Converts SELECT to COUNT rows format.
     *
     * @param string $select
     *
     * @return string
     */
    public function foundRowsSelect(string $select): string
    {
        $reg = '/^(SELECT )(.*)$/mi';
        $subst = '$1FOUND_ROWS() AS found_rows;';

        return preg_replace($reg, $subst, $select);
    }

    /**
     * Returns the name of function to get current date and time from DBMS.
     *
     * @return string
     */
    public function getCurrDate(): string
    {
        return 'NOW()';
    }

    /**
     * Gets the DSN string.
     *
     * @return string
     */
    public function getDsn(): string
    {
        return 'mysql:' . $this->getHostOrSocket() . ';dbname=' . $this->database;
    }

    /**
     * Converts SELECT command to its optimized form when limiting rows.
     *
     * @param string $select
     *
     * @return string
     */
    // public function paginatedSelect(string $select): string
    // {
    //     $reg = '/^(SELECT )(SQL_CALC_FOUND_ROWS ){0}((.*)( LIMIT [\d]+)( OFFSET [\d]+)?.*){1}$/mi';
    //     $subst = '$1SQL_CALC_FOUND_ROWS $3';

    //     return preg_replace($reg, $subst, $select);
    // }
}
