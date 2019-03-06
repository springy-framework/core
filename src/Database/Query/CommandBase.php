<?php

namespace Springy\Database\Query;

use Springy\Exceptions\SpringyException;

class CommandBase implements OperatorComparationInterface, OperatorGroupInterface
{
    /** @var array the list of columns */
    protected $columns = [];
    /** @var Conditions the conditions object */
    protected $conditions;
    /** @var string the table name */
    protected $table;
    /** @var string the alias for table name */
    protected $tableAlias;
    /** @var array the array of parameters filled after cast to string */
    protected $parameters;

    /**
     * Constructor.
     *
     * @param Conditions $conditions
     */
    public function __construct(Conditions $conditions = null)
    {
        $this->conditions = $conditions ?? new Conditions;
    }

    /**
     * Cast to string.
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__;
    }

    /**
     * Internal method to add a column to the list.
     *
     * @param string $statement
     * @param string $alias
     *
     * @return void
     */
    protected function addCol(string $statement, string $alias = null)
    {
        if ($alias !== null) {
            $statement .= ' AS '.$alias;
        }

        if (in_array($statement, $this->columns)) {
            throw new SpringyException('"'.$statement.'" already added to columns list');
        }

        $this->columns[] = $statement;
    }

    /**
     * Gets the table alias.
     *
     * Will returns the table name if alias is empty.
     *
     * @return string
     */
    protected function getTableAlias(): string
    {
        $alias = trim($this->tableAlias ?? '');

        if ($alias) {
            return $alias;
        }

        return $this->getTableName();
    }

    /**
     * Gets the table name.
     *
     * @throws SpringyException
     *
     * @return string
     */
    protected function getTableName(): string
    {
        if ($this->table === null) {
            throw new SpringyException('Table name can not be null');
        }

        $table = trim($this->table);

        if ($table === '') {
            throw new SpringyException('Empty table name');
        }

        return $table;
    }

    /**
     * Gets the table name with alias if applied.
     *
     * @return string
     */
    protected function getTableNameAndAlias(): string
    {
        $table = $this->getTableName();
        $alias = $this->getTableAlias();

        if ($alias !== $table) {
            $table .= ' AS '.$alias;
        }

        return $table;
    }

    /**
     * Returns a string with column names separated by comma.
     *
     * @return string
     */
    protected function strColumns(): string
    {
        if (!count($this->columns)) {
            throw new SpringyException('Empty columns set.');
        }

        return implode(', ', $this->columns);
    }

    /**
     * Adds a column.
     *
     * @param string $name
     * @param string $alias
     * @param bool   $addTable
     *
     * @return void
     */
    public function addColumn(string $name, string $alias = null, bool $addTable = true)
    {
        $name = ($addTable
            ? $this->getTableAlias().'.'
            : '').$name;

        $this->addCol($name, $alias);
    }

    /**
     * Adds COUNT($statement) function to columns list.
     *
     * @param string $statement
     * @param string $alias
     *
     * @return void
     */
    public function addCount(string $statement, string $alias = null)
    {
        $this->addCol('COUNT('.$statement.')', $alias);
    }

    /**
     * Adds a function or other statement to the columns list.
     *
     * @param string $statement
     * @param string $alias
     *
     * @return void
     */
    public function addFunction(string $statement, string $alias = null)
    {
        $this->addCol($statement, $alias);
    }

    /**
     * Adds a MAX($statement) function to the columns list.
     *
     * @param string $statement
     * @param string $alias
     *
     * @return void
     */
    public function addMax(string $statement, string $alias = null)
    {
        $this->addCol('MAX('.$statement.')', $alias);
    }

    /**
     * Adds a MIN($statement) function to the columns list.
     *
     * @param string $statement
     * @param string $alias
     *
     * @return void
     */
    public function addMin(string $statement, string $alias = null)
    {
        $this->addCol('MIN('.$statement.')', $alias);
    }

    /**
     * Adds a SUM($statement) function to the columns list.
     *
     * @param string $statement
     * @param string $alias
     *
     * @return void
     */
    public function addSum(string $statement, string $alias = null)
    {
        $this->addCol('SUM('.$statement.')', $alias);
    }

    /**
     * Returns the columns list.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Returns the list of parameters after cast to string.
     *
     * @return array
     */
    public function params(): array
    {
        return $this->parameters ?? $this->conditions->params();
    }

    /**
     * Converts the object to its string format.
     *
     * @return string
     */
    public function parse(): string
    {
        return $this->__toString();
    }

    /**
     * Sets the table name and alias if defined.
     *
     * @param string $table
     * @param string $alias
     *
     * @return void
     */
    public function setTable(string $table, string $alias = null)
    {
        $this->table = $table;
        $this->tableAlias = $alias;
    }
}
