<?php
/**
 * Migrator.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Migration;

use Springy\Database\Connection;
use Springy\Exceptions\SpringyException;
use Throwable;

class Migrator
{
    /** @var array the list of already applied revision scripts */
    protected $applied;
    /** @var Connection the database connection */
    protected $connection;
    /** @var string the name of migration control table */
    protected $controlTable;
    /** @var array the list of not applied yet revistion script */
    protected $notApplied;
    /** @var Revisions the revisions object */
    protected $revisions;

    /**
     * Constructor.
     *
     * @param string $dbIdentity
     */
    public function __construct(string $dbIdentity = null)
    {
        $identity = $dbIdentity ?? config_get('database.default');
        $path = config_get('database.connections.'.$identity.'.migration_dir');

        if ($path === null) {
            throw new SpringyException('Migration path configuration missing for "'.$identity.'"');
        }

        $this->applied = [];
        $this->notApplied = [];
        $this->connection = new Connection($identity);
        $this->revisions = new Revisions($path);
        $this->controlTable = config_get(
            'database.connections.'.$identity.'.migration_table',
            '_migration_control'
        );

        $this->checkControlTable();
        $this->checkAppliedRevisions();
    }

    /**
     * Checks about applied revisions.
     *
     * @return void
     */
    private function checkAppliedRevisions()
    {
        $command = 'SELECT done_at FROM '.$this->controlTable.' WHERE migration = ?';

        $this->revisions->rewind();

        while ($this->revisions->valid()) {
            $key = $this->revisions->key();
            $migration = $this->revisions->current();
            $result = $this->connection->select($command, [$migration->getIdentity()]);

            if (count($result)) {
                $this->revisions->setApplied($key);
                $this->revisions->next();

                continue;
            }

            $this->revisions->setNotApplied($key);
            $this->revisions->next();
        }
    }

    /**
     * Checks the existence of the control table.
     *
     * @throws SpringyException
     *
     * @return void
     */
    private function checkControlTable()
    {
        try {
            $this->connection->select('SELECT done_at FROM '.$this->controlTable.' LIMIT 1');

            return;
        } catch (Throwable $err) {
        }

        $command = 'CREATE TABLE '.$this->controlTable.'('.
            'migration VARCHAR(255) NOT NULL,'.
            'done_at DATETIME NOT NULL,'.
            'result_message VARCHAR(255),'.
            'PRIMARY KEY (migration)'.
            ')';

        try {
            $this->connection->run($command);
        } catch (Throwable $th) {
            throw new SpringyException('Can not create control table ('.$this->connection->getErrorCode().')');
        }
    }

    /**
     * Returns the quantity of applied revisions.
     *
     * @return int
     */
    public function getAppliedRevisionsCount(): int
    {
        return count($this->revisions->getApplied());
    }

    /**
     * Returns the quantity of not applied revisions.
     *
     * @return int
     */
    public function getNotAppliedRevisions(): int
    {
        return count($this->revisions->getNotApplied());
    }

    /**
     * Runs migration until defined version or all.
     *
     * @param string $version
     *
     * @return int
     */
    public function migrate($version = null): int
    {
        $revisions = $this->revisions->getNotApplied();
        $counter = 0;

        foreach ($revisions as $key) {
            $migration = $this->revisions->get($key);

            if ($version !== null && $migration->getVersion() > $version) {
                break;
            } elseif (!$migration->migrate($this->connection, $this->controlTable)) {
                break;
            }

            $this->revisions->setApplied($key);
            $counter += 1;
        }

        return $counter;
    }

    /**
     * Runs rollback until defined version or all.
     *
     * @param string $version
     *
     * @return int
     */
    public function rollback($version = null): int
    {
        $revisions = $this->revisions->getApplied();

        if ($version === null) {
            $version = 0;
        }

        $counter = 0;

        foreach ($revisions as $key) {
            $migration = $this->revisions->get($key);

            if ($migration->getVersion() < $version) {
                break;
            } elseif (!$migration->rollback($this->connection, $this->controlTable)) {
                break;
            }

            $this->revisions->setNotApplied($key);
            $counter += 1;
        }

        return $counter;
    }
}
