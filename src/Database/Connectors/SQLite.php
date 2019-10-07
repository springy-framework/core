<?php

/**
 * DBMS connector for SQLite databases.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Connectors;

use Springy\Exceptions\SpringyException;

/**
 * DBMS connector for SQLite databases.
 */
class SQLite extends Connector implements ConnectorInterface
{
    protected $encloseCharOpn = '"';
    protected $encloseCharCls = '"';

    /** @var int turning off connection tentative possible */
    protected $retries = 0;
    /** @var int sleep time in seconds between each try connection */
    protected $retrySleep = 0;

    /**
     * Returns the name of function to get current date and time from DBMS.
     *
     * @return string
     */
    public function getCurrDate(): string
    {
        return 'datetime(\'now\')';
    }

    /**
     * Gets the DSN string.
     *
     * @return string
     */
    public function getDsn(): string
    {
        return 'sqlite:' . $this->database;
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
        parent::setDatabase($database);

        if ($database !== ':memory:') {
            $path = realpath($database);
            if ($path === false) {
                throw new SpringyException('Database "' . $database . '" does not exists.');
            }
        }
    }
}
