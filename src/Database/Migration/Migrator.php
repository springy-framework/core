<?php

/**
 * Migrator.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Migration;

use Closure;
use Springy\Database\Connection;
use Springy\Exceptions\SpringyException;
use Throwable;

/**
 * Migrator.
 */
class Migrator
{
    /** @var array the list of already applied revision scripts */
    protected $applied;
    /** @var Connection the database connection */
    protected $connection;
    /** @var string the name of migration control table */
    protected $controlTable;
    /** @var string last occurred error */
    protected $error;
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
        $prefix = 'database.connections.';
        $identity = $dbIdentity ?? config_get('database.default');
        $path = config_get($prefix . $identity . '.migration.dir');
        $namespace = config_get($prefix . $identity . '.migration.namespace', 'App');

        if (is_null($path)) {
            throw new SpringyException(
                'Migration path configuration missing for "' . $identity . '"'
            );
        }

        $this->applied = [];
        $this->notApplied = [];
        $this->connection = new Connection($identity);
        $this->revisions = new Revisions($path, $namespace);
        $this->controlTable = config_get(
            $prefix . $identity . '.migration_table',
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
        $command = 'SELECT done_at FROM ' . $this->controlTable . ' WHERE migration = ?';

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
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     *
     * @throws SpringyException
     *
     * @return void
     */
    private function checkControlTable()
    {
        try {
            $this->connection->select('SELECT done_at FROM ' . $this->controlTable . ' LIMIT 1');
        } catch (Throwable $err) {
            // Table does not existis then try to creates it
            $command = 'CREATE TABLE ' . $this->controlTable . '('
                . 'migration VARCHAR(255) NOT NULL,'
                . 'done_at DATETIME NOT NULL,'
                . 'result_message VARCHAR(255),'
                . 'PRIMARY KEY (migration)'
                . ')';

            try {
                $this->connection->run($command);
            } catch (Throwable $th) {
                throw new SpringyException(
                    'Can not create control table (' . $this->connection->getError() . ')'
                );
            }
        }
    }

    public function countRevisionsUntil($version): int
    {
        $unapplied = $this->revisions->getNotApplied();

        // Count revisions to apply
        $toApply = 0;
        foreach ($unapplied as $key) {
            $migration = $this->revisions->get($key);

            if ($version !== null && $migration->getVersion() > $version) {
                break;
            }

            $toApply += 1;
        }

        return $toApply;
    }

    public function countRollbackUntil($version): int
    {
        $applied = $this->revisions->getApplied();

        if (is_null($version)) {
            $version = 0;
        }

        // Count revisions to undo
        $toUndo = 0;
        foreach ($applied as $key) {
            $migration = $this->revisions->get($key);

            if ($migration->getVersion() < $version) {
                break;
            }

            $toUndo += 1;
        }

        return $toUndo;
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
     * Gets the error message.
     *
     * @return void
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns the quantity of not applied revisions.
     *
     * @return int
     */
    public function getNotAppliedRevisionsCount(): int
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
    public function migrate($version = null, Closure $callback = null): int
    {
        $unapplied = $this->revisions->getNotApplied();

        // Count revisions to apply
        $toApply = $this->countRevisionsUntil($version);
        if ($toApply === 0) {
            return 0;
        }

        // Apply revisions
        $counter = 0;
        foreach ($unapplied as $key) {
            $migration = $this->revisions->get($key);

            if ($version !== null && $migration->getVersion() > $version) {
                break;
            } elseif (!$migration->migrate($this->connection, $this->controlTable)) {
                $this->error = $migration->getError() . ' at ' . $migration->getIdentity() . ' revision file';

                break;
            }

            $counter += 1;
            $this->revisions->setApplied($key);

            if ($callback instanceof Closure) {
                call_user_func($callback, $counter);
            }
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
    public function rollback($version = null, Closure $callback = null): int
    {
        $unapplied = $this->revisions->getApplied();

        // Count revisions to apply
        $toUndo = $this->countRollbackUntil($version);
        if ($toUndo === 0) {
            return 0;
        }

        if (is_null($version)) {
            $version = 0;
        }

        // Rolls back revisions
        $counter = 0;
        foreach ($unapplied as $key) {
            $migration = $this->revisions->get($key);

            if ($migration->getVersion() < $version) {
                break;
            } elseif (!$migration->rollback($this->connection, $this->controlTable)) {
                $this->error = $migration->getError() . ' at ' . $migration->getIdentity() . ' revision file';

                break;
            }

            $counter += 1;
            $this->revisions->setNotApplied($key);

            if ($callback instanceof Closure) {
                call_user_func($callback, $counter);
            }
        }

        return $counter;
    }
}
