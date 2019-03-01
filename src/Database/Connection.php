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
    /** @var array the record cache statement */
    private $cacheStatement;
    /** @var array connection instances */
    private static $conectionIds = [];
    /** @var string current identity connection */
    protected $identity;
    /** @var int default expiration time in seconds for cached select */
    private $cacheExpires;
    /** @var mixed last query execution error code */
    protected $lastErrorCode;
    /** @var mixed last query execution error information */
    protected $lastErrorInfo;
    /** @var array last query execution prepare statements */
    protected $lastValues;
    /** @var PDOStatement the prepared statement */
    protected $resSQL;

    /**
     * Constructor.
     *
     * @param string   $identity     database identity configuration key.
     * @param int|null $cacheExpires cached query expiration time in seconds.
     */
    public function __construct(string $identity = null, $cacheExpires = null)
    {
        $this->cacheExpires = $cacheExpires;
        $this->identity = $identity ?? config_get('database.default');
        $this->lastValues = [];

        $this->connect();
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->resSQL === null) {
            return;
        }

        $this->resSQL->closeCursor();
        $this->resSQL = null;
    }

    protected function getPdo(): PDO
    {
        if (!isset(self::$conectionIds[$this->identity])) {
            $this->connect();
        }

        return self::$conectionIds[$this->identity]->getPdo();
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
        if (isset(self::$conectionIds[$this->identity])) {
            return;
        }

        $config = Kernel::getInstance()->configuration();
        $drivers = [
            'mysql'  => 'MySQL',
            'sqlite' => 'SQLite',
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

    protected function saveQuery(string $query, int $cacheLifeTime = null)
    {
        if ((is_int($this->cacheExpires) || is_int($cacheLifeTime))
            && strtoupper(substr(ltrim($query), 0, 19)) == 'SELECT FOUND_ROWS()'
            && strtoupper(substr(ltrim($this->lastQuery), 0, 7)) == 'SELECT ') {
            $this->lastQuery = $query.'; /* '.md5(implode('//', array_merge([$this->lastQuery], $this->lastValues))).' */';

            return;
        }

        $this->lastQuery = $query;
    }

    protected function chkCache(int $cacheLifeTime = null)
    {
        // Clears the cache statement
        $this->cacheStatement = null;

        if ($cacheLifeTime === null || $this->cache['driver'] != 'memcached') {
            return;
        }

        $cacheKey = md5(implode('//', array_merge([$this->lastQuery], $this->lastValues)));

        try {
            $mmc = new Memcached();
            $mmc->addServer($this->cache['host'], $this->cache['port']);

            if ($sql = $mmc->get('dbCache_'.$cacheKey)) {
                $this->cacheStatement = $sql;
            }
        } catch (Exception $e) {
            $this->cacheStatement = null;
        }
    }

    protected function doExecute()
    {
        if ($this->cacheStatement !== null) {
            return;
        }

        $this->resSQL = $this->getPdo()->prepare($this->lastQuery);

        if ($this->resSQL === false) {
            $this->sqlErrorCode = $this->resSQL->errorCode();
            $this->sqlErrorInfo = $this->resSQL->errorInfo();

            throw new SpringyException('Can\'t prepare query.');
        }

        $this->prepare();
        $this->resSQL->closeCursor();

        try {
            $this->resSQL->execute();
        } catch (Throwable $err) {
            if ($this->isLostConnection($err)) {
                $this->connect();

                $this->resSQL->execute();

                return;
            }

            $this->sqlErrorCode = $this->resSQL->errorCode();
            $this->sqlErrorInfo = $this->resSQL->errorInfo();

            throw $err;
        };
    }

    protected function bindValue($key, $value, $param, &$counter)
    {
        if (is_numeric($key)) {
            $this->resSQL->bindValue(++$counter, $value, $param);

            return;
        }

        $this->resSQL->bindValue(':'.$key, $value, $param);
    }

    protected function prepare()
    {
        if (!count($this->lastValues)) {
            return;
        }

        $counter = 0;

        foreach ($this->lastValues as $key => $value) {
            switch (gettype($value)) {
                case 'boolean':
                    $param = \PDO::PARAM_BOOL;
                break;
                case 'integer':
                    $param = \PDO::PARAM_INT;
                break;
                case 'NULL':
                    $param = \PDO::PARAM_NULL;
                break;
                default:
                    $param = \PDO::PARAM_STR;
                break;
            }

            $this->bindValue($key, $value, $param, $counter);
        }
    }

    protected function saveCache(int $cacheLifeTime = null)
    {
        $lifeTime = $cacheLifeTime ?? $this->cacheExpires;

        if ($lifeTime === null
            || $this->cacheStatement !== null
            || $this->cache['driver'] != 'memcached'
            || strtoupper(substr(ltrim($this->lastQuery), 0, 7)) != 'SELECT ') {
            return;
        }

        $cacheKey = md5(implode('//', array_merge([$this->lastQuery], $this->lastValues)));

        try {
            $mmc = new Memcached();
            $mmc->addServer($this->cache['host'], $this->cache['port']);

            $this->cacheStatement = $this->fetchAll();

            $mmc->set('dbCache_'.$cacheKey, $this->cacheStatement, $lifeTime);

            $this->resSQL->closeCursor();
            $this->resSQL = null;
        } catch (Exception $err) {
            debug($this->lastQuery, 'Erro: '.$err->getMessage());
        }
    }

    /**
     * Executes a query.
     *
     * @param string   $query
     * @param array    $prepareParams
     * @param int|null $cacheLifeTime cache expiration time (in seconds) for SELECT queries or null for no cached query.
     *
     * @return bool
     */
    public function run(string $query, array $prepareParams = [], int $cacheLifeTime = null)
    {
        $this->lastErrorCode = null;
        $this->lastErrorInfo = null;
        $this->resSQL = null;

        $this->saveQuery($query, $cacheLifeTime);

        $this->lastValues = $prepareParams;

        $query = null;

        $this->chkCache($cacheLifeTime);
        $this->doExecute();
        $this->saveCache($cacheLifeTime);
    }

    /**
     * Returns all rows of the resultset.
     *
     * @param int $resultType
     *
     * @return array
     */
    public function fetchAll($resultType = PDO::FETCH_ASSOC): array
    {
        if ($this->cacheStatement !== null) {
            return $this->cacheStatement;
        } elseif ($this->resSQL instanceof PDOStatement) {
            return $this->resSQL->fetchAll($resultType);
        }

        return [];
    }
}
