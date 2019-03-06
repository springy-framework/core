<?php

namespace Springy\Database\Query;

use Springy\Exceptions\SpringyException;

class CommandBase implements OperatorComparationInterface, OperatorGroupInterface
{
    protected $columns = [];
    protected $conditions;
    protected $table;
    protected $tableAlias;
    protected $parameters;

    public function __construct(Conditions $conditions = null)
    {
        $this->conditions = $conditions ?? new Conditions;
    }

    public function __toString()
    {
        return __CLASS__;
    }

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

    protected function getTableAlias()
    {
        $alias = trim($this->tableAlias ?? '');

        if ($alias) {
            return $alias;
        }

        return $this->getTableName();
    }

    protected function getTableName()
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

    protected function getTableNameAndAlias()
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

    public function addColumn(string $name, string $alias = null, bool $addTable = true)
    {
        $name = ($addTable
            ? $this->getTableAlias().'.'
            : '').$name;

        $this->addCol($name, $alias);
    }

    public function addCount(string $statement, string $alias = null)
    {
        $this->addCol('COUNT('.$statement.')', $alias);
    }

    public function addFunction(string $statement, string $alias = null)
    {
        $this->addCol($statement, $alias);
    }

    public function addMax(string $statement, string $alias = null)
    {
        $this->addCol('MAX('.$statement.')', $alias);
    }

    public function addMin(string $statement, string $alias = null)
    {
        $this->addCol('MIN('.$statement.')', $alias);
    }

    public function addSum(string $statement, string $alias = null)
    {
        $this->addCol('SUM('.$statement.')', $alias);
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function params(): array
    {
        return $this->parameters ?? $this->conditions->params();
    }

    public function parse()
    {
        return $this->__toString();
    }

    public function setTable(string $table, string $alias = null)
    {
        $this->table = $table;
        $this->tableAlias = $alias;
    }
}
