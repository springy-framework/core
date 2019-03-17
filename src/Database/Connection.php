<?php
/**
 * Relational database access class.
 *
 * @copyright 2007 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Lucas Cardozo <lucas.cardozo@gmail.com>
 * @author    Allan Marques <allan.marques@ymail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   2.0.0
 */

namespace Springy\Database;

use Memcached;
use PDO;
use PDOStatement;
use Springy\Core\Kernel;
use Springy\Exceptions\SpringyException;
use Throwable;

class Connection
{
    use LostConnectionDetector;

    /** @var array cache configuration */
    protected $cache;
    /** @var int the cache life time for next SQL statement */
    protected $cacheLifeTime;
    /** @var int the style to fetch rows statement */
    protected $fetchStyle;
    /** @var string current identity connection */
    protected $identity;
    /** @var string last query execution error */
    protected $lastError;
    /** @var array last query execution prepare statements */
    protected $lastValues;
    /** @var PDOStatement|array the SQL statement */
    protected $statement;

    /** @var array connection instances */
    protected static $conectionIds = [];

    /**
     * Constructor.
     *
     * @param string $identity database identity configuration key.
     */
    public function __construct(string $identity = null)
    {
        $this->cacheLifeTime = 0;
        $this->fetchStyle = PDO::FETCH_ASSOC;
        $this->identity = $identity ?? config_get('database.default');
        $this->lastValues = [];

        $this->connect();
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->statement instanceof PDOStatement) {
            $this->statement->closeCursor();
        }

        $this->statement = null;
    }

    /**
     * Bind values to parameters.
     *
     * @return void
     */
    protected function bindParameters()
    {
        if (!count($this->lastValues)) {
            return;
        }

        $counter = 0;

        foreach ($this->lastValues as $key => $value) {
            switch (gettype($value)) {
                case 'boolean':
                    $param = PDO::PARAM_BOOL;
                break;
                case 'integer':
                    $param = PDO::PARAM_INT;
                break;
                case 'NULL':
                    $param = PDO::PARAM_NULL;
                break;
                default:
                    $param = PDO::PARAM_STR;
                break;
            }

            $this->bindValue($key, $value, $param, $counter);
        }
    }

    /**
     * Binds a value to a parameter.
     *
     * @param mixed $key
     * @param mixed $value
     * @param int   $param
     * @param int   $counter
     *
     * @return void
     */
    protected function bindValue($key, $value, $param, &$counter)
    {
        if (is_numeric($key)) {
            $this->statement->bindValue(++$counter, $value, $param);

            return;
        }

        $this->statement->bindValue(':'.$key, $value, $param);
    }

    /**
     * Reconnect to the database if a PDO connection is missing.
     *
     * @return void
     */
    protected function checkMissingConnection()
    {
        if ($this->getPdo() === null) {
            $this->connect();
        }
    }

    /**
     * Executes the quary again if is caused by lost connection.
     *
     * @param Throwable $err
     *
     * @throws PDOException
     *
     * @return void
     */
    protected function executeAgainIfLostConnection(Throwable $err)
    {
        if (!$this->isLostConnection($err)) {
            throw $err;
        }

        try {
            $this->lastError = null;
            $this->executeQuery();
        } catch (Throwable $err) {
            throw $err;
        }
    }

    /**
     * Executes the query.
     *
     * @throws PDOException
     *
     * @return void
     */
    protected function executeQuery()
    {
        try {
            $this->statement = $this->getPdo()->prepare(
                $this->lastQuery,
                [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]
            );

            $this->bindParameters();
            $this->statement->closeCursor();
            $this->statement->execute();
        } catch (Throwable $err) {
            $this->lastError = $err->getMessage();

            throw $err;
        }

        if ($this->cacheLifeTime) {
            $this->saveCache();
        }
    }

    /**
     * Gets the PDO object from current connection identity.
     *
     * @return PDO
     */
    protected function getPdo(): PDO
    {
        if (!isset(self::$conectionIds[$this->identity])) {
            $this->connect();
        }

        return self::$conectionIds[$this->identity]->getPdo();
    }

    /**
     * Loads rows from cache if applicable.
     *
     * @return void
     */
    protected function loadCache()
    {
        // Clears the cache statement
        $this->statement = null;

        if ($this->cacheLifeTime <= 0 || $this->cache['driver'] != 'memcached') {
            return;
        }

        $cacheKey = md5(implode('//', array_merge([$this->lastQuery], $this->lastValues)));

        try {
            $mmc = new Memcached();
            $mmc->addServer($this->cache['host'], $this->cache['port']);

            if ($sql = $mmc->get('dbCache_'.$cacheKey)) {
                $this->statement = $sql;
            }
        } catch (Exception $e) {
            $this->statement = null;
        }
    }

    /**
     * Saves the SQL statement rows in cache if applicable.
     *
     * @return void
     */
    protected function saveCache()
    {
        if ($this->cacheLifeTime <= 0 || $this->cache['driver'] != 'memcached') {
            return;
        }

        $cacheKey = md5(implode('//', array_merge([$this->lastQuery], $this->lastValues)));

        try {
            $mmc = new Memcached();
            $mmc->addServer($this->cache['host'], $this->cache['port']);

            $rows = $this->getAll();

            $mmc->set('dbCache_'.$cacheKey, $rows, $this->cacheLifeTime);

            $this->statement->closeCursor();
            $this->statement = $rows;
        } catch (Throwable $err) {
            debug($this->lastQuery, 'Erro: '.$err->getMessage());
        }
    }

    /**
     * Saves the query string in lastQuery property.
     *
     * @param string $query
     *
     * @return void
     */
    protected function setLastQuery(string $query)
    {
        if ($this->cacheLifeTime > 0
            && strtoupper(substr(ltrim($query), 0, 19)) == 'SELECT FOUND_ROWS()'
            && strtoupper(substr(ltrim($this->lastQuery), 0, 7)) == 'SELECT ') {
            $this->lastQuery = $query.'; /* '.md5(implode('//', array_merge([$this->lastQuery], $this->lastValues))).' */';

            return;
        }

        $this->lastQuery = $query;
    }

    /**
     * Connects to the DBMS.
     *
     * @throws SpringyException
     *
     * @return PDO|bool
     */
    public function connect()
    {
        // Is a connector to the identity?
        if (isset(self::$conectionIds[$this->identity])
            && self::$conectionIds[$this->identity]->getPdo() !== null) {
            return;
        }

        $config = Kernel::getInstance()->configuration();
        $drivers = [
            'mysql'      => 'MySQL',
            'pgsql'      => 'PostgreSQL',
            'postgre'    => 'PostgreSQL',
            'postgresql' => 'PostgreSQL',
            'sqlite'     => 'SQLite',
        ];

        $driver = $config->get('database.connections.'.$this->identity.'.driver');
        if ($driver === null) {
            throw new SpringyException('Database driver undefined.');
        } elseif (!isset($drivers[$driver])) {
            throw new SpringyException('Database driver not supported.');
        }

        $this->cache = $config->get('database.cache', [
            'driver' => 'none',
        ]);

        $driver = __NAMESPACE__.'\\Connectors\\'.$drivers[$driver];
        self::$conectionIds[$this->identity] = new $driver($config->get('database.connections.'.$this->identity));
        self::$conectionIds[$this->identity]->connect();
    }

    /**
     * Closes database connection.
     *
     * @return void
     */
    public function disconnect()
    {
        if (!isset(self::$conectionIds[$this->identity])) {
            return;
        }

        unset(self::$conectionIds[$this->identity]);
    }

    /**
     * Encloses the keyword by enclosure char to escapes it.
     *
     * @param string $keyword
     *
     * @return string
     */
    public function enclose(string $keyword): string
    {
        return $this->getConnector()->enclose($keyword);
    }

    /**
     * Returns true if connection was stablished.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        $connection = self::$conectionIds[$this->identity] ?? null;

        return ($connection !== null) and ($connection->getPdo() instanceof PDO);
    }

    /**
     * Begins a DB transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->getPdo()->beginTransaction();
    }

    /**
     * Commits a DB transaction.
     *
     * @return void
     */
    public function commit()
    {
        $this->getPdo()->commit();
    }

    /**
     * Rolls back a DB transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        $this->getPdo()->rollBack();
    }

    /**
     * Executes a query.
     *
     * @param string $query
     * @param array  $params
     *
     * @return void
     */
    public function run(string $query, array $params = [])
    {
        $this->lastError = null;
        $this->statement = null;

        $this->setLastQuery($query);

        $this->lastValues = $params;

        $query = null;

        $this->loadCache();
        if ($this->statement !== null) {
            return;
        }

        $this->checkMissingConnection();

        try {
            $this->executeQuery();
        } catch (Throwable $err) {
            $this->executeAgainIfLostConnection($err);
        }
    }

    /**
     * Runs an delete query and returns the quantity of rows affected.
     *
     * @param string $query
     * @param array  $params
     *
     * @return int
     */
    public function delete(string $query, array $params = []): int
    {
        $this->run($query, $params);

        return $this->affectedRows();
    }

    /**
     * Runs a insert query and returns the quantity of rows inserted.
     *
     * @param string $query
     * @param array  $params
     *
     * @return int
     */
    public function insert(string $query, array $params = []): int
    {
        $this->run($query, $params);

        return $this->affectedRows();
    }

    /**
     * Runs a select query and returns the array of found rows.
     *
     * @param string $query
     * @param array  $params
     * @param int    $fetchStyle
     * @param int    $cacheLifeTime
     *
     * @return array
     */
    public function select(
        string $query,
        array $params = [],
        int $fetchStyle = null,
        int $cacheLifeTime = 0
    ): array {
        $this->cacheLifeTime = $cacheLifeTime;
        $this->run($query, $params);
        $this->fetchStyle = $fetchStyle ?? $this->fetchStyle;
        $this->statement = $this->getAll();
        $this->cacheLifeTime = 0;

        return $this->statement ?? [];
    }

    /**
     * Runs an update query and returns the quantity of rows affected.
     *
     * @param string $query
     * @param array  $params
     *
     * @return int
     */
    public function update(string $query, array $params = []): int
    {
        $this->run($query, $params);

        return $this->affectedRows();
    }

    /**
     * Returns the number of rows affected by the last SQL statement.
     *
     * @return int
     */
    public function affectedRows(): int
    {
        if ($this->statement instanceof PDOStatement) {
            return $this->statement->rowCount();
        } elseif (is_array($this->statement)) {
            return count($this->statement);
        }

        return 0;
    }

    /**
     * Returns the current row of the statement and moves the cursor to the next row.
     *
     * @return array|bool
     */
    public function fetch()
    {
        if ($this->statement instanceof PDOStatement) {
            $this->getAll();
        }

        $current = current($this->statement);
        next($this->statement);

        return $current;
    }

    /**
     * Returns all rows of the resultset.
     *
     * @return array|bool
     */
    public function getAll()
    {
        if ($this->statement instanceof PDOStatement) {
            $rows = $this->statement->fetchAll($this->fetchStyle);
            $this->statement->closeCursor();
            $this->statement = $rows;
        }

        return $this->statement;
    }

    /**
     * Returns the value of a column.
     *
     * @param mixed $var
     *
     * @return mixed
     */
    public function getColumn($var)
    {
        if ($this->statement instanceof PDOStatement) {
            $this->getAll();
        }

        $current = current($this->statement);

        return $current[$var] ?? null;
    }

    /**
     * Gets the connector object.
     *
     * @return Connector
     */
    public function getConnector()
    {
        if (!isset(self::$conectionIds[$this->identity])) {
            $this->connect();
        }

        return self::$conectionIds[$this->identity];
    }

    /**
     * Returns the current row of the resultset and moves the cursor to next record.
     *
     * @return array|bool
     */
    public function getCurrent()
    {
        if ($this->statement instanceof PDOStatement) {
            $this->getAll();
        }

        return current($this->statement);
    }

    /**
     * Returns the database driver name of the current connection.
     *
     * @return mixed
     */
    public function getDriverName()
    {
        return $this->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Returns the last error occurred.
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->lastError ?? '';
    }

    /**
     * Resets the cursor to the first row of the statement and returns it.
     *
     * @return array|bool
     */
    public function getFirst()
    {
        if ($this->statement instanceof PDOStatement) {
            $this->getAll();
        }

        return reset($this->statement);
    }

    /**
     * Moves the cursor to the last row of the statement and returns it.
     *
     * @return array|bool
     */
    public function getLast()
    {
        if ($this->statement instanceof PDOStatement) {
            $this->getAll();
        }

        return end($this->statement);
    }

    /**
     * Returns the value of the auto increment columns in last INSERT.
     *
     * @param string $name
     *
     * @return int
     */
    public function getLastInsertedId(string $name = null)
    {
        return $this->getPdo()->lastInsertId($name);
    }

    /**
     * Returns the last executed query.
     *
     * @return string
     */
    public function getLastQuery(): string
    {
        return $this->lastQuery ?? '';
    }

    /**
     * Returns the next row of the statement.
     *
     * Be careful when using this method because it moves the cursos before fetch the record.
     *
     * @return array|bool
     */
    public function getNext()
    {
        if ($this->statement instanceof PDOStatement) {
            $this->getAll();
        }

        return next($this->statement);
    }

    /**
     * Moves the cursor the previous row of the statement and returns it.
     *
     * @return array|bool
     */
    public function getPrev()
    {
        if ($this->statement instanceof PDOStatement) {
            $this->getAll();
        }

        return prev($this->statement);
    }

    /**
     * Returns the DBMS version informations.
     *
     * @return mixed
     */
    public function getServerVersion()
    {
        return $this->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
    }
}
