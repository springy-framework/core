<?php
/**
 * SQL INSERT class constructor.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database\Query;

use Springy\Database\Connection;
use Springy\Exceptions\SpringyException;

class Insert extends CommandBase implements OperatorComparationInterface, OperatorGroupInterface
{
    protected $connection;
    protected $parameters;
    protected $values;

    public function __construct(Connection $connection, string $table = null)
    {
        $this->connection = $connection;
        $this->parameters = [];
        $this->table = $table;
        $this->values = [];
    }

    public function __toString()
    {
        $this->parameters = [];

        $insert = 'INSERT INTO '.$this->getTableName()
            .'('.$this->strColumns().') VALUES '
            .$this->strValues();

        return $insert;
    }

    protected function addValueObj(Value $value)
    {
        if (!in_array($value->getColumn(), $this->columns)) {
            $this->addCol($value->getColumn());
        }

        if (current($this->values) === false) {
            $this->values[] = [];
            end($this->values);
        }

        $key = key($this->values);

        if (isset($this->values[$key][$value->getColumn()])) {
            throw new SpringyException('Value "'.$value->getColumn().'" redeclared.');
        }

        $this->values[$key][$value->getColumn()] = $value;
    }

    protected function strColValues(array $values)
    {
        if (!count($values)) {
            throw new SpringyException('Empty column values set.');
        } elseif (count($this->columns) !== count($values)) {
            throw new SpringyException('Columns and values quantity does not mismatch.');
        }

        $strValues = '(';

        foreach ($this->columns as $column) {
            if (!isset($values[$column])) {
                throw new SpringyException('Column "'.$column.'" has no correspondent value.');
            }

            $value = $values[$column];

            if ($value->isExpression()) {
                $strValues .= $value->getValue().',';

                continue;
            }

            $strValues .= '?, ';
            $this->parameters[] = $value->getValue();
        }

        return rtrim($strValues, ', ').')';
    }

    protected function strValues()
    {
        if (!count($this->values)) {
            throw new SpringyException('Empty values set.');
        }

        $multiValues = [];
        foreach ($this->values as $values) {
            $multiValues[] = $this->strColValues($values);
        }

        return implode(', ', $multiValues);
    }

    /**
     * Adds a value to the current values list.
     *
     * @param string $column       the column name.
     * @param mixed  $value        the value of the condition.
     * @param bool   $isExpression set this true to define the value as a column name or a function
     *
     * @return void
     */
    public function addValue(
        string $column,
        $value = null,
        bool $isExpression = false
    ) {
        $this->addValueObj(new Value($column, $value, $isExpression));
    }

    public function addValues(array $values)
    {
        $this->values[] = [];
        end($this->values);

        foreach ($values as $value) {
            if (!($value instanceof Value)) {
                throw new SpringyException('The values must be instances of Springy\Database\Query\Value class.');
            }

            $this->addValueObj($value);
        }
    }
}
