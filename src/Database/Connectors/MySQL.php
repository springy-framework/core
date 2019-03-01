<?php
/**
 * DMBS connector for MySQL servers.
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
        $this->options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES \''.($config['charset'] ?? 'utf8').'\'';
        $this->options[PDO::ATTR_PERSISTENT] = $config['persistent'] ?? true;

        $this->socket = false;
        $this->setHost($config);
        $this->setDatabase($config['database'] ?? '');
        $this->port = $config['port'] ?? 3128;
        $this->setUsername($config['username'] ?? '');
        $this->setPassword($config['password'] ?? '');
        $this->retries = $config['retries'] ?? 3;
        $this->retrySleep = $config['retry_sleep'] ?? 1;
    }

    /**
     * Gets string for HOST or SOCKET connector DSN.
     *
     * @return string
     */
    protected function getHostOrSocket(): string
    {
        return $this->socket
            ? 'unix_socket='.$this->socket
            : 'host='.$this->host.';port='.$this->port;
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
            return $this->setRoundRobin($config);
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
            return $this->setRoundRobin($config);
        }

        if (!$socket) {
            throw new SpringyException('Database socket server undefined.');
        }

        $this->socket = $socket;
    }

    /**
     * Gets the DSN string.
     *
     * @return string
     */
    public function getDsn(): string
    {
        return 'mysql:'.$this->getHostOrSocket().';dbname='.$this->database;
    }
}
