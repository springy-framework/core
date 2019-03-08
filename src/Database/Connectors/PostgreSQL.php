<?php
/**
 * DBMS connector for PostgreSQL servers.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Connectors;

use PDO;

class PostgreSQL extends Connector implements ConnectorInterface
{
    protected $encloseCharOpn = '"';
    protected $encloseCharCls = '"';

    /** @var string|array the database host server */
    protected $host;
    /** @var int the database server port */
    protected $port;
    /** @var int connection tentative possible */
    protected $retries;
    /** @var int sleep time in seconds between each try connection */
    protected $retrySleep;
    /** @var string schema configuration */
    protected $schema;
    /** @var string the SSL options */
    protected $ssl;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->charset = $config['charset'] ?? 'UTF8';
        $this->port = $config['port'] ?? '5432';
        $this->retries = $config['retries'] ?? 3;
        $this->retrySleep = $config['retry_sleep'] ?? 1;
        $this->schema = $config['schema'] ?? '';
        $this->setHost($config);

        unset($this->options[PDO::ATTR_EMULATE_PREPARES]);

        $this->ssl = '';

        foreach (['sslmode', 'sslcert', 'sslkey', 'sslrootcert'] as $option) {
            if (isset($config[$option])) {
                $this->ssl .= ';'.$option.'='.$config[$option];
            }
        }
    }

    /**
     * Configures database connection after the connection stablished.
     *
     * @return void
     */
    protected function afterConnectSettings()
    {
        if ($this->charset) {
            $this->pdo->prepare('SET NAMES \''.$this->charset.'\'')->execute();
        }

        if ($this->timezone) {
            $this->pdo->prepare('SET TIME ZONE \''.$this->timezone.'\'')->execute();
        }

        if ($this->schema) {
            $this->pdo->prepare('SET SCHEMA \''.$this->schema.'\'')->execute();
        }
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
        $host = ($config['host'] ?? '');

        if (is_array($host)) {
            $host = $this->setRoundRobin($host);
        }

        $this->host = $host;
    }

    /**
     * Converts SELECT to COUNT rows format.
     *
     * No needed for PostgreSQL if counter are in each rows when using OVER() clause.
     *
     * @param string $select
     *
     * @return string
     */
    public function foundRowsSelect(string $select): string
    {
        $reg = '/^(SELECT )(.*)$/mi';
        $subst = '';

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
        return 'pgsql:'.($this->host ? 'host='.$this->host : '')
            .';port='.$this->port.';dbname='.$this->database.$this->ssl;
    }

    /**
     * Converts SELECT command to its optimized form when limiting rows.
     *
     * @param string $select
     *
     * @return string
     */
    public function paginatedSelect(string $select): string
    {
        $reg = '/^(SELECT )(.+)( FROM (.*)( LIMIT [\d]+)( OFFSET [\d]+)?.*){1}$/mi';
        $subst = '$1$2, COUNT(*) OVER() AS found_rows$3';

        return preg_replace($reg, $subst, $select);
    }
}
