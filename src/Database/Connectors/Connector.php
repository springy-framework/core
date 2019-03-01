<?php
/**
 * DMBS connector basic implementation.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Connectors;

use PDO;

class Connector
{
    /** @var string name of the database */
    protected $database;
    /** @var array PDO constructor options */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];
    /** @var string the database user password */
    protected $password;
    /** @var PDO the PDO object */
    protected $pdo;
    /** @var string the database username */
    protected $username;

    /**
     * Connects with the database.
     *
     * @return PDO
     */
    public function connect(): PDO
    {
        do {
            try {
                $this->pdo = new PDO($this->getDsn(), $this->username, $this->password, $this->options);
            } catch (Exception $exception) {
                if ($this->retries) {
                    $this->retries -= 1;
                    sleep($this->retrySleep);

                    continue;
                }

                throw $exception;
            }
        } while ($this->pdo === null);

        return $this->pdo;
    }

    /**
     * Gets the database name.
     *
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * Returns the database user password.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Returns the PDO object.
     *
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Returns the database username.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
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
        if (!$database) {
            throw new SpringyException('Database name undefined.');
        }

        $this->database = $database;
    }

    /**
     * Sets the database user password.
     *
     * @param string $password
     *
     * @return void
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * Sets the database username.
     *
     * @param string $username
     *
     * @return void
     */
    public function setUsername(string $username)
    {
        if (!$username) {
            throw new SpringyException('Database username undefined.');
        }

        $this->username = $username;
    }
}
