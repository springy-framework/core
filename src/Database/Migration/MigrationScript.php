<?php
/**
 * Migration script.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Migration;

use Closure;
use DateTime;
use Springy\Database\Connection;
use Springy\Exceptions\SpringyException;
use Symfony\Component\Yaml\Yaml;

class MigrationScript
{
    /** @var string error message */
    private $error;
    /** @var string the name of the script */
    private $script;
    /** @var mixed the migration script */
    private $mScript;
    /** @var mixed the rollback script */
    private $rScript;
    /** @var string the migration verstion */
    private $version;

    /**
     * Constructor.
     *
     * @param string $path
     * @param string $version
     * @param string $script
     */
    public function __construct(string $path, string $version, string $script)
    {
        $this->script = $script;
        $this->version = $version;

        $this->getScript($path.DS.$version.DS.$script);
    }

    /**
     * Get the script.
     *
     * @param string $path
     *
     * @return void
     */
    private function getScript(string $path)
    {
        if (!is_file($path)) {
            return;
        }

        $ext = pathinfo($this->script, PATHINFO_EXTENSION);

        if (in_array($ext, ['yml', 'yaml'])) {
            $yaml = Yaml::parseFile($path);

            $this->mScript = $yaml['migrate'] ?? null;
            $this->rScript = $yaml['rollback'] ?? null;

            return;
        } elseif ($ext === 'sql') {
            $this->mScript = file_get_contents($path);
        } elseif ($ext === 'php') {
            require_once $path;

            $this->loadScript();
        }
    }

    /**
     * Loads the PHP script.
     *
     * @return void
     */
    private function loadScript()
    {
        $namespace = 'App\\Migrations\\Rev'.$this->version.'\\'.pathinfo($this->script, PATHINFO_FILENAME);

        $script = new $namespace();

        if (is_callable([$script, 'migrate'])) {
            $this->mScript = function (Connection $connection) use ($script) {
                return call_user_func([$script, 'migrate'], $connection);
            };
        }

        if (is_callable([$script, 'rollback'])) {
            $this->rScript = function (Connection $connection) use ($script) {
                return call_user_func([$script, 'rollback'], $connection);
            };
        }
    }

    /**
     * Runs the script.
     *
     * @param Connection $connection
     * @param mixed      $script
     *
     * @return bool
     */
    private function runScript(Connection $connection, $script): bool
    {
        if ($script instanceof Closure) {
            return call_user_func($script, $connection);
        }

        $scripts = is_array($script) ? $script : [$script];

        foreach ($scripts as $sql) {
            if (!$this->runSql($connection, $sql)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Runs a SQL script.
     *
     * @param Connection $connection
     * @param string     $sql
     *
     * @return bool
     */
    private function runSql(Connection $connection, string $sql): bool
    {
        try {
            $connection->run($sql);

            if (!$connection->getErrorCode()) {
                return true;
            }

            $this->error = $connection->getErrorCode();
        } catch (Throwable $err) {
            $this->error = '['.$err->getCode().'] '.$err->getMessage();
        }

        return false;
    }

    /**
     * Returns the migration script identity.
     *
     * @return string
     */
    public function getIdentity(): string
    {
        return $this->version.'/'.$this->script;
    }

    /**
     * Returns the migration script version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Runs migration script.
     *
     * @param Connection $connection
     * @param string     $table
     *
     * @return bool
     */
    public function migrate(Connection $connection, string $table): bool
    {
        if ($this->mScript === null) {
            $this->error = 'Migration script "'.$this->getIdentity().'" has no migration method.';

            return false;
        }

        if (!$this->runScript($connection, $this->mScript)) {
            return false;
        }

        $command = 'INSERT INTO '.$table
            .' (migration, done_at, result_message) VALUES (?, ?, ?)';

        $now = new DateTime();

        $connection->insert($command, [
            $this->getIdentity(),
            $now->format('Y-m-d H:i:s.u'),
            $connection->affectedRows().' affected rows',
        ]);

        return true;
    }

    /**
     * Runs the rollback script.
     *
     * @param Connection $connection
     * @param string     $table
     *
     * @return bool
     */
    public function rollback(Connection $connection, string $table): bool
    {
        if ($this->rScript === null) {
            $this->error = 'Migration script "'.$this->getIdentity().'" has no rollback method.';

            return false;
        }

        if (!$this->runScript($connection, $this->rScript)) {
            return false;
        }

        $command = 'DELETE FROM '.$table
            .' WHERE migration = ?';

        $connection->delete($command, [
            $this->getIdentity(),
        ]);

        return true;
    }
}
