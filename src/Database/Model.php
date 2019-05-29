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

use DateTime;
use Springy\Core\Configuration;
use Springy\Database\Query\Conditions;
use Springy\Database\Query\Delete;
use Springy\Database\Query\Embed;
use Springy\Database\Query\Insert;
use Springy\Database\Query\Select;
use Springy\Database\Query\Update;
use Springy\Database\Query\Where;
use Springy\Exceptions\SpringyException;

class Model extends RowsIterator
{
    /**
     * The table name.
     *
     * Must be defined in the heir class.
     *
     * @var string
     */
    protected $table;

    /**
     * Protects against great load data set.
     *
     * @var bool
     */
    protected $abortOnEmptyFilter = true;

    /**
     * The default database configuration identity.
     *
     * @var string
     */
    protected $dbIdentity;

    /**
     * Default limit rows for selects.
     *
     * @var int
     */
    protected $defaultLimit;

    /** @var array the list of objects to embed */
    protected $embeds;
    /** @var array the group by elements */
    protected $groupBy;
    /** @var Conditions the having filter elements */
    protected $having;
    /** @var array the list of joins */
    protected $joins;
    /** @var bool informs whether the record was loaded */
    protected $loaded;
    /** @var array the list of columns to the select query */
    protected $selectColumns = [];

    /** @var static instance for prevent more than one parse */
    protected static $instance;

    // Trigger names
    const TG_AFT_DEL = 'triggerAfterDelete';
    const TG_AFT_INS = 'triggerAfterInsert';
    const TG_AFT_UPD = 'triggerAfterUpdate';
    const TG_BEF_DEL = 'triggerBeforeDelete';
    const TG_BEF_INS = 'triggerBeforeInsert';
    const TG_BEF_UPD = 'triggerBeforeUpdate';

    /**
     * Constructor.
     *
     * @param Where $filter
     */
    public function __construct($filter = null, string $dbIdentity = null)
    {
        if (static::$instance === null) {
            $strucPath = Configuration::getInstance()->get(
                'database.model_structures',
                __DIR__.DS.'structures'
            ).DS.preg_replace('/[^\\w_-]/', '', $this->table).'.json';
            parent::__construct($strucPath);

            $this->embeds = [];
            $this->dbIdentity = $dbIdentity ?? $this->dbIdentity;
            $this->groupBy = [];
            $this->having = new Conditions();
            $this->joins = [];
            $this->loaded = false;

            static::$instance = clone $this;
        }

        $thisRef = &$this;
        $thisRef = clone static::$instance;

        if ($filter === null) {
            return;
        }

        $this->load($filter);
    }

    /**
     * Builds the SQL object to delete rows with given condition.
     *
     * @param Where $where
     *
     * @return Delete|Update
     */
    protected function buildDelete(Where $where)
    {
        if ($this->deletedColumn) {
            $delete = new Update(new Connection($this->dbIdentity), $this->table);
            $delete->addValue($this->deletedColumn, 1);
            $delete->setWhere($where);

            return $delete;
        }

        $delete = new Delete(new Connection($this->dbIdentity), $this->table);
        $delete->setWhere($where);

        return $delete;
    }

    /**
     * Calls a user trigger if exists and returns its result.
     *
     * @param string $triggerName
     *
     * @return bool
     */
    protected function checkTrigger(string $triggerName): bool
    {
        if ($this->bypassTriggers || !is_callable([$this, $triggerName])) {
            return true;
        }

        return call_user_func([$this, $triggerName]) ?? true;
    }

    /**
     * Deletes one or many rows by one single command or navagating through the rows.
     *
     * @param Where $where
     *
     * @return int
     */
    protected function deleteRows(Where $where): int
    {
        $triggers = [static::TG_BEF_DEL, static::TG_AFT_DEL];

        // Execute a single SQL command if has no triggers
        if ($this->bypassTriggers || !$this->hasAnyTrigger($triggers)) {
            $delete = $this->buildDelete($where);

            return $delete->run();
        }

        // Navigate through found rows and deletes it
        $couter = 0;
        $model = new static();
        $model->select($where);
        while ($model->valid()) {
            $couter += $model->delete();
            $model->next();
        }

        return $couter;
    }

    /**
     * Returns the SELECT statement command object.
     *
     * @param Where $where
     * @param array $orderby
     * @param int   $offset
     * @param int   $limit
     *
     * @return Select
     */
    protected function getSelect(Where $where, array $orderby, int $offset, int $limit): Select
    {
        $select = new Select(new Connection($this->dbIdentity), $this->table);

        foreach ($this->getSelectColumns() as $column) {
            $select->addColumn($column);
        }

        foreach ($this->joins as $join) {
            $select->addJoin($join);
        }

        $select->setWhere($where);

        foreach ($this->groupBy as $col) {
            $select->addGroupBy($col);
        }

        $select->setHaving($this->having);

        foreach ($orderby as $key => $value) {
            $select->addOrderBy($key, $value);
        }

        $select->setLimit($limit);
        $select->setOffset($offset);

        return $select;
    }

    /**
     * Returns the array of columns to the select.
     *
     * @return array
     */
    protected function getSelectColumns(): array
    {
        if (count($this->selectColumns)) {
            return $this->selectColumns;
        }

        $columns = [];
        foreach ($this->columns as $name => $data) {
            if ($data->computed ?? false) {
                continue;
            }

            $columns[] = $name;
        }

        if (!count($columns)) {
            throw new SpringyException('No columns defined');
        }

        return $columns;
    }

    /**
     * Builds the condition by given data.
     *
     * @param Where|int|string|array|null $where
     *
     * @throws SpringyException
     *
     * @return Where
     */
    protected function getWhere($where = null): Where
    {
        $this->currentKey = [];

        if ($where instanceof Where) {
            return $where;
        } elseif ((is_int($where) || is_string($where)) && count($this->primaryKey) === 1) {
            $this->currentKey[] = $where;
            $where = new Where();
            $where->add($this->primaryKey[0], $this->currentKey[0]);
        } elseif (is_array($where) && count($this->primaryKey) === count($where)) {
            $this->currentKey = $where;
            $where = new Where();
            foreach ($this->primaryKey as $key => $value) {
                $where->add($value, $this->currentKey[$key]);
            }
        }

        if ($where instanceof Where) {
            return $where;
        }

        throw new SpringyException('Invalid condition.');
    }

    /**
     * Builds a Where object from primary key of the current row.
     *
     * @return Where
     */
    protected function getWhereFromRow(): Where
    {
        if (!$this->hasPK()) {
            throw new SpringyException('Current row has no primary key.');
        }

        $row = current($this->rows);
        $where = new Where();

        foreach ($this->primaryKey as $column) {
            $where->add($column, $row[$column]);
        }

        return $where;
    }

    /**
     * Checks whether a triggers of the list exists.
     *
     * @param array $triggers
     *
     * @return bool
     */
    protected function hasAnyTrigger(array $triggers): bool
    {
        foreach ($triggers as $trigger) {
            if (is_callable([$this, $trigger])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Inserts current row data as new record.
     *
     * @return int
     */
    protected function insertRow(): int
    {
        if (!$this->checkTrigger(static::TG_BEF_INS)) {
            return 0;
        }

        $connection = new Connection($this->dbIdentity);
        $insert = new Insert($connection, $this->table);

        $this->setCmdValues($insert);

        $res = $insert->run();

        if ($res === 1) {
            $lid = $connection->getLastInsertedId();

            if ($lid && count($this->primaryKey) === 1) {
                $this->load($lid);
            } elseif ($this->hasPK()) {
                $this->load($this->getPK());
            }

            $this->checkTrigger(static::TG_AFT_INS);
        }

        return $res;
    }

    /**
     * Adds a value to SQL command.
     *
     * @param object $command
     * @param string $column
     *
     * @return void
     */
    protected function setCmdColVal($command, string $column)
    {
        $key = key($this->rows);
        $row = $this->rows[$key];
        $changed = $this->changed[$key][$column] ?? false;

        if (isset($row[$column]) && ($this->newRecord || $changed)) {
            $command->addValue($column, $row[$column]);
        } elseif ($this->newRecord && $column == $this->addedDateColumn) {
            $date = new DateTime();
            $command->addValue($column, $date->format('Y-m-d H:i:s.u'));
        } elseif ($this->newRecord && $column == $this->deletedColumn) {
            $command->addValue($column, 0);
        }
    }

    /**
     * Sets SQL command values.
     *
     * @param object $command
     *
     * @return void
     */
    protected function setCmdValues($command)
    {
        foreach ($this->columns as $column => $properties) {
            if ($properties->computed ?? false) {
                continue;
            }

            $this->setCmdColVal($command, $column);
        }
    }

    /**
     * Embeds data in rows.
     *
     * @return void
     */
    protected function setEmbeddings()
    {
        if (!count($this->embeds)) {
            return;
        }

        foreach ($this->embeds as $embed) {
            $attr = $embed->getEmbedName();
            $refc = $embed->getRefColumn();

            $keys = [];
            foreach ($this->rows as $index => $row) {
                if (!$embed->isEligible($row)) {
                    continue;
                }

                if (!in_array($row[$refc], $keys)) {
                    $keys[] = $row[$refc];
                }
            }

            if (!count($keys)) {
                continue;
            }

            $embed->setFilter($keys);
            $embed->select();

            foreach ($this->rows as $index => $row) {
                $this->rows[$index][$attr] = $embed->getResult($row);
            }
        }
    }

    /**
     * Updates current row data to database record.
     *
     * @return int
     */
    protected function updateRow(): int
    {
        if (!$this->checkTrigger(static::TG_BEF_UPD)) {
            return 0;
        }

        $connection = new Connection($this->dbIdentity);
        $update = new Update($connection, $this->table);

        $pkVal = $this->getPK();
        foreach ($this->primaryKey as $index => $column) {
            $update->addCondition($column, $pkVal[$index]);
        }

        $this->setCmdValues($update);

        $res = $update->run();

        if ($res === 1) {
            $clone = new static($pkVal);
            if (!$clone->isLoaded()) {
                return 0;
            }

            $key = key($this->rows);
            $this->rows[$key] = $clone->get();
            unset($this->changed[$key]);

            $this->checkTrigger(static::TG_AFT_UPD);
        }

        return $res;
    }

    /**
     * Adds a statement to the group by clause.
     *
     * @param string $statement
     *
     * @return void
     */
    public function addGroupBy(string $statement)
    {
        $this->groupBy[] = $statement;
    }

    /**
     * Adds a Embed object.
     *
     * @param Embed $embed
     *
     * @return void
     */
    public function addEmbed(Embed $embed)
    {
        $this->embeds[] = $embed;
    }

    /**
     * Adds a statement to the having clause.
     *
     * @param string $statement
     * @param mixed  $value
     * @param string $operator
     * @param string $expression
     *
     * @return void
     */
    public function addHaving(
        string $statement,
        $value = null,
        string $operator = self::OP_EQUAL,
        string $expression = self::COND_AND
    ) {
        $this->having->add($statement, $value, $operator, $expression);
    }

    /**
     * Adds a Join object to the list.
     *
     * @param Join $join
     *
     * @return void
     */
    public function addJoin(Join $join)
    {
        $this->joins[] = $join;
    }

    /**
     * Clears the group by clause.
     *
     * @return void
     */
    public function clearGroupBy()
    {
        $this->groupBy = [];
    }

    /**
     * Clears the having clause.
     *
     * @return void
     */
    public function clearHaving()
    {
        $this->having->clear();
    }

    /**
     * Clears the join clause.
     *
     * @return void
     */
    public function clearJoin()
    {
        $this->joins = [];
    }

    /**
     * Delete and return affected rows.
     *
     * Deletes the current row or by given key.
     *
     * @param int|string|Where|null $where
     *
     * @return int
     */
    public function delete($where = null): int
    {
        $current = false;

        if ($where === null) {
            $where = $this->getWhereFromRow();
            $current = true;
        }

        $filter = $this->getWhere($where);
        $current = $this->isCurrent($current);

        if (!$current) {
            return $this->deleteRows($filter);
        }

        $delete = $this->buildDelete($filter);

        if (!$this->checkTrigger(static::TG_BEF_DEL)) {
            return 0;
        }

        $result = $delete->run();

        $this->checkTrigger(static::TG_AFT_DEL);

        return $result;
    }

    /**
     * Returns whether the desired record has been loaded.
     *
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Loads a record.
     *
     * @param mixed $where
     *
     * @return bool True if one and only one row was found or false in other case.
     */
    public function load($where): bool
    {
        $filter = $this->getWhere($where);

        $this->select($filter);
        $this->loaded = ($this->foundRows === 1);

        return $this->loaded;
    }

    /**
     * Saves current row changes.
     *
     * @return int
     */
    public function save(): int
    {
        if (!$this->valid() || !isset($this->changed[key($this->rows)])) {
            return 0;
        }

        if (!$this->validate()) {
            return 0;
        }

        if ($this->newRecord) {
            return $this->insertRow();
        } elseif (!$this->hasPK()) {
            return 0;
        }

        return $this->updateRow();
    }

    /**
     * Performs a select.
     *
     * @param Where $where
     * @param array $orderby a multidimentional array with column => 'ASC|DESC' pairs.
     * @param int   $offset
     * @param int   $limit
     *
     * @return void
     */
    public function select(Where $where = null, array $orderby = null, int $offset = 0, int $limit = null)
    {
        $this->changed = [];
        $this->loaded = false;
        $this->newRecord = false;
        $this->rows = [];

        $limit = $limit ?? $this->defaultLimit ?? 0;

        if ($this->abortOnEmptyFilter && !$where->count() && !$limit) {
            return;
        }

        if ($this->deletedColumn && !$where->get($this->deletedColumn)) {
            $where->add($this->deletedColumn, 0);
        }

        $select = $this->getSelect($where, $orderby ?? [], $offset, $limit);

        $this->rows = $select->run(true);
        $this->foundRows = $select->foundRows();

        $this->computeRows();
        $this->setEmbeddings();

        return $this->rows;
    }

    /**
     * Sets the columns list for select.
     *
     * @param array $columns
     *
     * @return void
     */
    public function setColumns(array $columns)
    {
        $this->selectColumns = [];

        foreach ($columns as $name) {
            $column = $this->columns->$name ?? null;

            if (null === $column) {
                throw new SpringyException('Column "'.$name.'" does not defined in model.');
            } elseif ($column->computed ?? false) {
                throw new SpringyException('Column "'.$name.'" is computed.');
            }

            $this->selectColumns[] = $name;
        }
    }

    /**
     * Turns the fetch mode as object on|off.
     *
     * @param bool $asObject
     *
     * @return void
     */
    public function setFetchAsObject(bool $asObject)
    {
        $this->fetchAsObject = $asObject;
    }

    /**
     * Sets the group by clause list.
     *
     * @param array $groupBy
     *
     * @return void
     */
    public function setGroupBy(array $groupBy)
    {
        $this->groupBy = [];

        foreach ($groupBy as $col) {
            $this->addGroupBy($col);
        }
    }

    /**
     * Sets the having clause conditions.
     *
     * @param Conditions $having
     *
     * @return void
     */
    public function setHaving(Conditions $having)
    {
        $this->having = $having;
    }
}
