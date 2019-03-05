<?php
/**
 * SQL SELECT class constructor.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/fernandoval/Springy/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\Database;

use Springy\Exceptions\SpringyException;

class Select extends DatabaseCommand implements OperatorComparationInterface, OperatorGroupInterface
{
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    protected $connection;
    protected $groupBy;
    protected $having;
    protected $joins;
    protected $offset;
    protected $orderBy;
    protected $limit;
    protected $parameters;
    protected $rows;

    public function __construct(Connection $connection, string $table = null, string $alias = null)
    {
        $this->connection = $connection;
        $this->having = new Conditions();
        $this->groupBy = [];
        $this->joins = [];
        $this->offset = 0;
        $this->orderBy = [];
        $this->parameters = [];
        $this->table = $table;
        $this->tableAlias = $alias;
        $this->rows = [];

        parent::__construct();
    }

    public function __toString()
    {
        $this->parameters = [];

        $select = 'SELECT '.$this->strColumns()
            .' FROM '.$this->getTableNameAndAlias()
            .$this->strJoins()
            .$this->strWhere()
            .$this->strOrderBy()
            .$this->strGroupBy()
            .$this->strHaving()
            .($this->limit ? ' LIMIT '.$this->limit : '')
            .($this->offset ? ' OFFSET '.$this->offset : '');

        return $select;
    }

    protected function strGroupBy(): string
    {
        if (!count($this->groupBy)) {
            return '';
        }

        return ' GROUP BY '.implode(', ', $this->groupBy);
    }

    protected function strHaving(): string
    {
        $having = $this->having->parse();
        $this->parameters = array_merge($this->parameters, $this->having->params());

        return $having ? ' HAVING '.$this->having : '';
    }

    protected function strJoins(): string
    {
        $joins = '';
        foreach ($this->joins as $join) {
            $joins .= ' '.$join;

            $this->parameters = array_merge($this->parameters, $join->params());
        }

        return $joins;
    }

    protected function strOrderBy(): string
    {
        if (!count($this->orderBy)) {
            return '';
        }

        return ' ORDER BY '.implode(', ', $this->orderBy);
    }

    protected function strWhere(): string
    {
        $where = $this->conditions->parse();
        $this->parameters = $this->conditions->params();

        return $where;
    }

    protected function testLimit(int $limit): int
    {
        if ($limit < 0) {
            throw new SpringyException('Negative limit value.');
        }

        return $limit;
    }

    protected function testOffset(int $offset): int
    {
        if ($offset < 0) {
            throw new SpringyException('Negative offset value.');
        }

        return $offset;
    }

    public function addGroupBy(string $statement)
    {
        if (in_array($statement, $this->groupBy)) {
            return;
        }

        $this->groupBy[] = $statement;
    }

    public function addHaving(
        string $statement,
        $value = null,
        string $operator = self::OP_EQUAL,
        string $expression = self::COND_AND
    ) {
        $this->having->add($statement, $value, $operator, $expression);
    }

    public function addJoin(Join $join)
    {
        foreach ($join->getColumns() as $col) {
            $this->addCol($col);
        }

        $this->joins[] = $join;
    }

    public function addOrderBy(string $name, string $direction = self::ORDER_ASC)
    {
        $this->orderBy[] = $name.($direction != self::ORDER_ASC ? ' '.$direction : '');
    }

    public function foundRows(): int
    {
        $select = $this->parseFoundRows();

        if ($select === '' && count($this->rows) > 0 && in_array('found_rows', $this->rows[0])) {
            return (int) $this->rows[0]['found_rows'];
        }

        return (int) $this->connection->select($select, $this->parameters)[0]['found_rows'];
    }

    public function parseFoundRows(): string
    {
        $this->parameters = [];

        $select = 'SELECT '.$this->strColumns()
        .' FROM '.$this->getTableNameAndAlias()
        .$this->strJoins()
        .$this->strWhere();

        return $this->connection->getConnector()->foundRowsSelect($select);
    }

    public function parsePaginated(): string
    {
        return $this->connection->getConnector()->paginatedSelect($this->__toString());
    }

    public function run(bool $paginated = false): array
    {
        $select = ($paginated ? $this->parsePaginated() : $this->__toString());

        $this->rows = $this->connection->select($select, $this->parameters);

        return $this->rows;
    }

    public function setGroupBy(array $groupBy)
    {
        $this->groupBy = [];

        foreach ($groupBy as $col) {
            $this->addGroupBy($col);
        }
    }

    public function setHaving(Conditions $having)
    {
        $this->having = $having;
    }

    public function setLimit(int $limit = null)
    {
        if ($limit === null) {
            $this->limit = $limit;
        }

        $this->limit = $this->testLimit($limit);
    }

    public function setOffset(int $offset)
    {
        $this->offset = $this->testOffset($offset);
    }

    public function setOrderBy(array $orderBy)
    {
        $this->orderBy = [];

        foreach ($orderBy as $statement => $direction) {
            $this->addOrderBy($statement, $direction);
        }
    }

    public function setWhere(Where $where)
    {
        $this->conditions = $where;
    }
}
