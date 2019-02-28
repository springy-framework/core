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

use Springy\Core\Kernel;
use Springy\Exceptions\SpringyException;

class Connection
{
    /** @var array connection instances */
    private static $conectionIds = [];
    /** @var array connection fail control */
    private static $conErrors = [];

    /// Tempo em segundos da validade do cache
    private $cacheExpires = null;
    /// Entrada de configuração de banco atual
    private $database = false;
    /// Recurso de conexão atual
    private $dataConnect = false;

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
        $this->database = $identity;
        $this->dataConnect = $this->connect($this->identity ?? config_get('database.default'));
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

    /**
     * Connects to the DBMS.
     *
     * @param string $identity database identity configuration key.
     *
     * @throws SpringyException
     *
     * @return PDO|bool
     */
    public function connect(string $identity)
    {
        // The DBMS is marked with error?
        if (isset(self::$conErrors[$identity])) {
            return false;
        }
        // Is a connector to the identity?
        elseif (isset(self::$conectionIds[$identity])) {
            return self::$conectionIds[$identity]['PDO'];
        }

        $config = Kernel::getInstance()->configuration();
        $drivers = [
            'mysql' => 'MySQL',
        ];

        $driver = $config->get('database.connections.'.$identity.'.driver');
        if ($driver === null) {
            throw new SpringyException('Database engine undefined.');
        } elseif (!isset($drivers[$driver])) {
            throw new SpringyException('Database engine not supported.');
        }

        $driver = __NAMESPACE__.'\\Connectors\\'.$drivers[$driver];
        $connector = new $driver($config->get('database.'.$identity), $config->get('main.charset'));

        self::$conectionIds[$identity] = [
            'PDO'      => $connector->getPdo(),
            'database' => $connector->getDatabase(),
        ];

        return true;
    }
}
