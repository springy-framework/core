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

        parent::__construct();
    }

    public function __toString()
    {
        $select = 'SELECT '.$this->strColumns()
            .' FROM '.$this->getTableNameAndAlias()
            .$this->getJoins()
            .$this->getWhere()
            .$this->getOrderBy()
            .$this->getGroupBy()
            .$this->getHaving()
            .($this->limit ? ' LIMIT '.$this->limit : '')
            .($this->offset ? ' OFFSET '.$this->offset : '');

        return $select;
    }

    protected function getGroupBy(): string
    {
        if (!count($this->groupBy)) {
            return '';
        }

        return ' GROUP BY '.implode(', ', $this->groupBy);
    }

    protected function getHaving(): string
    {
        $having = $this->having->parse();
        $this->parameters = array_merge($this->parameters, $this->having->params());

        return $having ? ' HAVING '.$this->having : '';
    }

    protected function getJoins(): string
    {
        $joins = '';
        foreach ($this->joins as $join) {
            $joins .= ' '.$join;

            $this->parameters = array_merge($this->parameters, $join->params());
        }

        return $joins;
    }

    protected function getOrderBy(): string
    {
        if (!count($this->orderBy)) {
            return '';
        }

        return ' ORDER BY '.implode(', ', $this->orderBy);
    }

    protected function getWhere(): string
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
