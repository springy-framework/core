<?php
/**
 * DMBS connector for SQLite databases.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Connectors;

use Springy\Exceptions\SpringyException;

class SQLite extends Connector implements ConnectorInterface
{
    /** @var int turning off connection tentative possible */
    protected $retries = 0;
    /** @var int sleep time in seconds between each try connection */
    protected $retrySleep = 0;

    /**
     * Gets the DSN string.
     *
     * @return string
     */
    public function getDsn(): string
    {
        return 'sqlite:'.$this->database;
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
                throw new SpringyException('Database "'.$database.'" does not exists.');
            }
        }
    }
}
