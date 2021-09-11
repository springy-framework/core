<?php

/**
 * SQL DELETE command constructor.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

use Springy\Database\Connection;
use Springy\Exceptions\SpringyException;

/**
 * SQL DELETE command constructor class.
 */
class Delete extends CommandBase implements OperatorComparationInterface, OperatorGroupInterface
{
    /** @var Connection the connection object */
    protected $connection;
    /** @var array the array of parameters filled after cast to string */
    protected $parameters;
    /** @var bool throws error if there is no condition */
    protected $safeMode;

    /**
     * Constructor.
     *
     * @param Connection $connection
     * @param string     $table
     */
    public function __construct(Connection $connection, string $table = null)
    {
        $this->conditions = new Where();
        $this->connection = $connection;
        $this->parameters = [];
        $this->safeMode = true;
        $this->table = $table;
    }

    /**
     * Converts the object to its string form.
     *
     * @return string
     */
    public function __toString()
    {
        $this->parameters = [];

        $update = 'DELETE FROM ' . $this->getTableNameAndAlias() . $this->strWhere();

        return $update;
    }

    /**
     * Returns the WHERE clause string.
     *
     * @return string
     */
    protected function strWhere(): string
    {
        if ($this->safeMode && !$this->conditions->count()) {
            throw new SpringyException('DELETE without conditions is dangerous.');
        }

        $where = $this->conditions->parse();
        $this->parameters = array_merge($this->parameters, $this->conditions->params());

        return $where;
    }

    /**
     * Adds a condition to the conditions list.
     *
     * @param string $column      the column name.
     * @param mixed  $value       the value of the condition.
     * @param string $operator    the comparison operator.
     * @param string $expression  the expression to put before this condition.
     * @param bool   $compareCols set this true to define the value as a column name or a function
     *
     * @return void
     */
    public function addCondition(
        string $column,
        $value = null,
        string $operator = self::OP_EQUAL,
        string $expression = self::COND_AND,
        bool $compareCols = false
    ) {
        $this->conditions->add($column, $value, $operator, $expression, $compareCols);
    }

    /**
     * Executes the DELETE command and returns the quantity of affected rows.
     *
     * @return int
     */
    public function execute(): int
    {
        return $this->connection->execute($this->__toString(), $this->parameters);
    }

    /**
     * Turns the safe delete mode on|off.
     *
     * @param bool $safe
     *
     * @return void
     */
    public function setSafeMode(bool $safe)
    {
        $this->safeMode = $safe;
    }

    /**
     * Sets teh where condition.
     *
     * @param Where $where
     *
     * @return void
     */
    public function setWhere(Where $where)
    {
        $this->conditions = $where;
    }
}
