<?php
/**
 * Parent class for relational database table model objects.
 *
 * @copyright 2014 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @author    Allan Marques <allan.marques@ymail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   3.0.0
 */

namespace Springy\Database;

use Iterator;
use Springy\Exceptions\SpringyException;
use Springy\Utils\MessageContainer;
use Springy\Validation\Validator;

class RowsIterator implements Iterator
{
    /**
     * The columns structure.
     *
     * Will be loaded from JSON file.
     *
     * @var object
     */
    protected $columns;

    /**
     * The name of column used as soft delete control.
     *
     * If undefined the model will catch them from columns list
     * searching for column where property "softDelete": true.
     *
     * @var string
     */
    protected $deletedColumn;

    /**
     * Determines what get method does when desired column does not exists.
     *
     * If true, a exception will be throwed.
     * If false, a null value will be returned.
     *
     * @var bool
     */
    protected $errorIfColNotExists = false;

    /**
     * Determines the return mode of the fetch methods.
     *
     * If true, the rows are returned as object.
     * If false, the rows are returned as array.
     *
     * @var bool
     */
    protected $fetchAsObject = false;

    /**
     * The name of column used as date when row was created.
     *
     * If undefined the model will catch them from columns list
     * searching for column where property "insertedAt": true.
     *
     * @var string
     */
    protected $addedDateColumn;

    /**
     * The primary key columns list.
     *
     * If empty the model will catch them from columns list
     * searching for columns where property "primaryKey": true.
     *
     * @var array
     */
    protected $primaryKey = [];

    /**
     * The list of writable columns.
     *
     * If undefined the model will catch them from columns list
     * searching for column where properties 'readOnly' and 'computed'
     * are false or undefined.
     *
     * @var array
     */
    protected $writableColumns = [];

    /** @var bool turns triggers execution off */
    protected $bypassTriggers;
    /** @var array the changed rows and columns */
    protected $changed;
    /** @var array the list of computed columns */
    protected $computedCols;
    /** @var array the current row key */
    protected $currentKey;
    /** @var int the number of rows found by last select */
    protected $foundRows;
    /** @var bool determines when the one row is a new record */
    protected $newRecord;
    /** @var array group by columns */
    protected $rows;
    /** @var MessageContainer the last validation errors */
    protected $validationErrors;

    /**
     * Constructor.
     */
    public function __construct(string $structure)
    {
        $this->bypassTriggers = false;
        $this->changed = [];
        $this->columns = json_decode(file_get_contents($structure));
        $this->foundRows = 0;
        $this->newRecord = false;
        $this->rows = [];

        $this->setColsPK();
        $this->setComputedCols();
        $this->setInsertDateCol();
        $this->setDeletedCol();
        $this->setWritableCols();
    }

    /**
     * Magic method to get value from columns as if they were properties.
     *
     * This method will use the get() method.
     *
     * @param string $name the name of the column.
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Magic method to set value in columns as if they were properties..
     *
     * This method will use the set() method.
     *
     * @param string $name  the column name.
     * @param mixed  $value the value.
     *
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Fills calculated columns of the current row.
     *
     * @return void
     */
    protected function computeCols()
    {
        if (!count($this->computedCols) || !$this->valid()) {
            return;
        }

        $key = key($this->rows);

        foreach ($this->computedCols as $colName) {
            $column = $this->columns->$colName ?? null;
            if (null === $column) {
                $this->rows[$key][$colName] = null;

                continue;
            }

            $callable = $column->computed ?? null;
            if (null === $callable || !is_callable([$this, $callable])) {
                $this->rows[$key][$colName] = null;

                continue;
            }

            $this->rows[$key][$colName] = call_user_func([$this, $callable], $this->rows[$key]);
        }
    }

    /**
     * Fetches the current row as array or object.
     *
     * @param array|bool $row
     *
     * @return array|object|bool
     */
    protected function fetchRow($row)
    {
        if (is_bool($row)) {
            return $row;
        } elseif ($this->fetchAsObject) {
            return (object) $row;
        }

        return $row;
    }

    /**
     * Gets an array with primary key values.
     *
     * @return array
     */
    protected function getPK(): array
    {
        $pkVal = [];
        $row = current($this->rows);

        foreach ($this->primaryKey as $column) {
            if (!isset($row[$column]) || $row[$column] === null) {
                return $pkVal;
            }

            $pkVal[] = $row[$column];
        }

        return $pkVal;
    }

    /**
     * Gets the validation rules array from columns structure.
     *
     * @return array
     */
    protected function getValidationRules(): array
    {
        $rules = [];

        foreach ($this->columns as $name => $properties) {
            $validation = $properties->validation ?? null;
            if (!$validation) {
                continue;
            }

            $rules[$name] = $validation;
        }

        if (is_callable([$this, 'validationRules'])) {
            $rules = call_user_func([$this, 'validationRules'], $rules);
        }

        return $rules;
    }

    /**
     * Checks whether the primary key is set in the current row.
     *
     * @return bool
     */
    protected function hasPK(): bool
    {
        if (!$this->valid() || !count($this->primaryKey)) {
            return false;
        }

        $pkVal = $this->getPK();

        return count($pkVal) === count($this->primaryKey);
    }

    /**
     * Returns the result of hook funcion or the value.
     *
     * @param string $column
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function hook(string $column, $value)
    {
        $column = $this->columns->$column;
        $hook = $column->hook ?? false;

        if ($hook && is_callable([$this, $hook])) {
            return call_user_func([$this, $hook], $value);
        }

        return $value;
    }

    /**
     * Checks if current key corresponds to current row.
     *
     * @param bool $current ignores the verification and assumes as true.
     *
     * @return bool
     */
    protected function isCurrent(bool $current): bool
    {
        if ($current) {
            return true;
        }

        if (!count($this->currentKey) || !$this->valid() || !$this->hasPK()) {
            return false;
        }

        $row = current($this->rows);

        foreach ($this->primaryKey as $index => $column) {
            if ($row[$column] != $this->currentKey[$index]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Builds the primary key columns array if not defined.
     *
     * @return void
     */
    protected function setColsPK()
    {
        if (count($this->primaryKey)) {
            return;
        }

        foreach ($this->columns as $name => $properties) {
            if ($properties->primaryKey ?? false) {
                $this->primaryKey[] = $name;
            }
        }
    }

    /**
     * Determines the soft deleted column if not defined.
     *
     * @return void
     */
    protected function setDeletedCol()
    {
        if (is_string($this->deletedColumn)) {
            return;
        }

        foreach ($this->columns as $name => $properties) {
            if ($properties->softDelete ?? false) {
                $this->deletedColumn = $name;

                return;
            }
        }
    }

    /**
     * Build the array of computed columns list.
     *
     * @return void
     */
    protected function setComputedCols()
    {
        if (is_array($this->computedCols)) {
            return;
        }

        $this->computedCols = [];

        foreach ($this->columns as $name => $properties) {
            if ($properties->computed ?? false) {
                $this->computedCols[] = $name;
            }
        }
    }

    /**
     * Determines the added date column if not defined.
     *
     * @return void
     */
    protected function setInsertDateCol()
    {
        if (is_string($this->addedDateColumn)) {
            return;
        }

        foreach ($this->columns as $name => $properties) {
            if ($properties->insertedAt ?? false) {
                $this->addedDateColumn = $name;

                return;
            }
        }
    }

    /**
     * Determines the writable columns if not defined.
     *
     * @return void
     */
    protected function setWritableCols()
    {
        if (count($this->writableColumns)) {
            return;
        }

        foreach ($this->columns as $name => $properties) {
            if (($properties->readOnly ?? false) || ($properties->computed ?? false)) {
                continue;
            }

            $this->writableColumns[] = $name;
        }
    }

    /**
     * Returns the number of records found by the last select.
     *
     * @return int
     */
    public function foundRows(): int
    {
        return $this->foundRows;
    }

    /**
     * Gets a column or a row from the resultset.
     *
     * @param string $column the name of the desired column or null to all columns of the current record.
     *
     * @return mixed
     */
    public function get(string $column = null)
    {
        if ($column === null) {
            return $this->current();
        }

        $columns = current($this->rows);

        if (!isset($columns[$column]) && $this->errorIfColNotExists) {
            throw new SpringyException('Column "'.$column.'" does not exists.');
        }

        return $columns[$column] ?? null;
    }

    /**
     * Returns all data in a given column.
     *
     * @return array
     */
    public function getAllColumn(string $column)
    {
        return array_column($this->rows, $column);
    }

    /**
     * Returns an array with the primary key columns.
     *
     * @return array
     */
    public function getPKColumns(): array
    {
        return $this->primaryKey;
    }

    /**
     * Returns all rows.
     *
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * Returns the last validation error messages container.
     *
     * @return MessageContainer
     */
    public function getValidationErrors(): MessageContainer
    {
        return $this->validationErrors ?? new MessageContainer();
    }

    /**
     * Returns the number of rows.
     *
     * @return int
     */
    public function rowsCount(): int
    {
        return count($this->rows);
    }

    /**
     * Sets the value of a column.
     *
     * @param string $column the name of the column.
     * @param mixed  $value  the value of the column.
     *
     * @throws SpringyException
     *
     * @return void
     */
    public function set(string $column, $value = null)
    {
        if (!isset($this->columns->$column)) {
            throw new SpringyException('Column "'.$column.'" does not exists.');
        } elseif (!in_array($column, $this->writableColumns)) {
            throw new SpringyException('Column "'.$column.'" is not writable.');
        }

        if (empty($this->rows)) {
            $this->newRecord = true;
            $this->rows[] = [];
        }

        $key = key($this->rows);
        $current = $this->rows[$key][$column] ?? null;
        $isset = isset($this->rows[$key][$column]);

        if ($current == $value && $isset) {
            return;
        }

        $this->rows[$key][$column] = $this->hook($column, $value);
        $this->changed[$key][$column] = true;
    }

    /**
     * Sets multiple columns value.
     *
     * @param array $columns
     *
     * @return void
     */
    public function setRow(array $columns)
    {
        foreach ($columns as $column => $value) {
            $this->set($column, $value);
        }
    }

    /**
     * Sets the rows of the iterator.
     *
     * @param array $rows
     *
     * @return void
     */
    public function setRows(array $rows)
    {
        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                throw new SpringyException('Incorrect row format at index ('.$index.').');
            }

            $this->setRow($row);
        }
    }

    /**
     * Sets the values of the computed columns for all rows.
     *
     * @return void
     */
    public function computeRows()
    {
        if (!count($this->computedCols)) {
            return;
        }

        reset($this->rows);

        while (current($this->rows)) {
            $this->computeCols();

            next($this->rows);
        }

        reset($this->rows);
    }

    /**
     * Gets the current record.
     *
     * @return array|object|bool
     */
    public function current()
    {
        return $this->fetchRow(current($this->rows));
    }

    /**
     * Moves the pointer to the last record.
     *
     * @return void
     */
    public function end()
    {
        end($this->rows);
    }

    /**
     * Moves the pointer to the next record.
     *
     * @return void
     */
    public function next()
    {
        next($this->rows);
    }

    /**
     * Moves the pointer to the previous record.
     *
     * @return void
     */
    public function prev()
    {
        prev($this->rows);
    }

    /**
     * Rewinds rows cursor to first position.
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->rows);
    }

    /**
     * Gets the names of the columns.
     *
     * @return array|bool
     */
    public function key(): array
    {
        if (!count($this->rows)) {
            return [];
        }

        return array_keys($this->rows[0]);
    }

    /**
     * Checks whether the current record exists.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() !== false;
    }

    /**
     * Performs the validation of the data in the object.
     *
     * @return bool
     */
    public function validate(): bool
    {
        if (!$this->valid()) {
            return false;
        }

        $validation = new Validator(
            $this->current(),
            $this->getValidationRules()
        );

        $result = $validation->validate();

        $this->validationErrors = $validation->getErrors();

        return $result;
    }
}
