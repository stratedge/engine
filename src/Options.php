<?php
namespace Stratedge\Engine;

use Stratedge\Engine\Interfaces\Options as OptionsInterface;
use Stratedge\Engine\Factory;
use Stratedge\Toolbox\NumberUtils;

class Options implements OptionsInterface
{
    const DIR_ASC       =   'ASC';
    const DIR_DESC      =   'DESC';
    const COND_AND      =   'AND';
    const COND_OR       =   'OR';
    const JOIN          =   'JOIN';
    const INNER_JOIN    =   'INNER_JOIN';
    const LEFT_JOIN     =   'LEFT_JOIN';
    const RIGHT_JOIN    =   'RIGHT_JOIN';

    protected $where = [];
    protected $data = [];
    protected $group_by = [];
    protected $having = [];
    protected $order_by = [];
    protected $max;
    protected $offset;
    protected $alias;
    protected $join = [];


    /**
     * SHORTHAND METHODS
     */


    public function where($stmt, $data = [])
    {
        $this->setWhere($stmt);

        if (!empty($data)) {
            $this->addData($data);
        }

        return $this;
    }


    public function andWhere($stmt, $data = [])
    {
        $this->addWhere($stmt, self::COND_AND);

        if (!empty($data)) {
            $this->addData($data);
        }

        return $this;
    }


    public function orWhere($stmt, $data = [])
    {
        $this->addWhere($stmt, self::COND_OR);

        if (!empty($data)) {
            $this->addData($data);
        }

        return $this;
    }


    public function data($key, $value = null)
    {
        $this->addData($key, $value);

        return $this;
    }


    public function groupBy($column)
    {
        $this->setGroupBy($column, $dir);

        return $this;
    }


    public function having($stmt, $data = [])
    {
        $this->setHaving($stmt);

        if (!empty($data)) {
            $this->addData($data);
        }

        return $this;
    }


    public function andHaving($stmt, $data = [])
    {
        $this->addHaving($stmt, self::COND_AND);

        if (!empty($data)) {
            $this->addData($data);
        }

        return $this;
    }


    public function orHaving($stmt, $data = [])
    {
        $this->addHaving($stmt, self::COND_OR);

        if (!empty($data)) {
            $this->addData($data);
        }

        return $this;
    }


    public function orderBy($column, $dir = self::DIR_ASC)
    {
        $this->setOrderBy($column, $dir);

        return $this;
    }


    public function orderByAsc($column)
    {
        $this->setOrderBy($column, self::DIR_ASC);

        return $this;
    }


    public function orderByDesc($column)
    {
        $this->setOrderBy($column, self::DIR_DESC);

        return $this;
    }


    public function limit($max, $offset = null)
    {
        $this->setMax($max);

        if (!is_null($offset)) {
            $this->setOffset($offset);
        }

        return $this;
    }


    public function alias($alias)
    {
        $this->alias = $alias;

        return $alias;
    }


    public function join($table, $alias, $on, $from_alias = null)
    {
        $this->addJoin(self::JOIN, $table, $alias, $on, $from_alias);

        return $this;
    }


    public function innerJoin($table, $alias, $on, $from_alias = null)
    {
        $this->addJoin(self::INNER_JOIN, $table, $alias, $on, $from_alias);

        return $this;
    }


    public function leftJoin($table, $alias, $on, $from_alias = null)
    {
        $this->addJoin(self::LEFT_JOIN, $table, $alias, $on, $from_alias);

        return $this;
    }


    public function rightJoin($table, $alias, $on, $from_alias = null)
    {
        $this->addJoin(self::RIGHT_JOIN, $table, $alias, $on, $from_alias);

        return $this;
    }


    /**
     * WHERE METHODS
     */


    public function getWhere()
    {
        return $this->where;
    }


    protected function setWhere($stmt)
    {
        $this->where = [
            ['type' => self::COND_AND, 'stmt' => $stmt]
        ];

        return $this;
    }


    protected function addWhere($stmt, $type = self::COND_AND)
    {
        $this->where = array_merge($this->where, [
            ['type' => $type, 'stmt' => $stmt]
        ]);

        return $this;
    }


    /**
     * DATA METHODS
     */


    public function getData()
    {
        return $this->data;
    }


    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = $key;
        }

        if (is_string($key)) {
            $this->data = [$key => $value];
        }

        return $this;
    }


    public function addData($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        }

        if (is_string($key)) {
            $this->data[$key] = $value;
        }

        return $this;
    }


    /**
     * GROUP BY METHODS
     */


    public function getGroupBy()
    {
        return $this->group_by;
    }


    public function setGroupBy($column)
    {
        if (is_array($column)) {
            $this->group_by = $column;
        }

        if (is_string($column)) {
            $this->group_by = [$column];
        }

        return $this;
    }


    public function addGroupBy($column)
    {
        if (is_array($column)) {
            $this->group_by = array_merge($this->group_by, $column);
        }

        if (is_string($column)) {
            $this->group_by[] = $column;
        }

        return $this;
    }


    /**
     * HAVING METHODS
     */
    

    public function getHaving()
    {
        return $this->having;
    }


    protected function setHaving($stmt)
    {
        $this->having = [
            ['type' => self::COND_AND, 'stmt' => $stmt]
        ];

        return $this;
    }


    protected function addHaving($stmt, $type = self::COND_AND)
    {
        $this->having = array_merge($this->having, [
            ['type' => $type, 'stmt' => $stmt]
        ]);

        return $this;
    }


    /**
     * ORDER BY METHODS
     */
    

    public function getOrderBy()
    {
        return $this->order_by;
    }


    public function setOrderBy($column, $dir = self::DIR_ASC)
    {
        $this->order_by = [
            ['column' => $column, 'dir' => $dir]
        ];

        return $this;
    }


    public function addOrderBy($column, $dir = self::DIR_ASC)
    {
        $this->order_by[] = ['column' => $column, 'dir' => $dir];

        return $this;
    }


    /**
     * LIMIT METHODS
     */
    

    public function getMax()
    {
        return $this->max;
    }


    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }


    public function getOffset()
    {
        return $this->offset;
    }


    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }


    /**
     * ALIAS METHODS
     */
    

    public function getAlias()
    {
        return $this->alias;
    }


    /**
     * JOIN METHODS
     */
    

    public function getJoin()
    {
        return $this->join;
    }


    public function addJoin($type, $table, $alias, $on, $from_alias)
    {
        $this->join[] = [
            'type' => $type,
            'table' => $table,
            'alias' => $alias,
            'on' => $on,
            'from_alias' => $from_alias ?: $this->getAlias()
        ];

        return $this;
    }


    /**
     * UTILITY METHODS
     */
    

    public function isAnd($conditional)
    {
        return is_array($conditional) &&
               isset($conditional['type']) &&
               $conditional['type'] === self::COND_AND;
    }


    public function isOr($conditional)
    {
        return is_array($conditional) &&
               isset($conditional['type']) &&
               $conditional['type'] === self::COND_OR;
    }


    public function resolveCondition($conditional)
    {
        if (!is_array($conditional) || empty($conditional['stmt'])) {
            return null;
        }

        return $conditional['stmt'];
    }


    public function resolveColumn($order)
    {
        if (!is_array($order) || empty($order['column'])) {
            return null;
        }

        return $order['column'];
    }


    public function resolveDirection($order)
    {
        if (!is_array($order) || empty($order['dir'])) {
            return null;
        }

        return $order['dir'];
    }


    public function isJoin($join)
    {
        return is_array($join) &&
               !empty($join['type']) &&
               $join['type'] === self::JOIN;
    }

    
    public function isInnerJoin($join)
    {
        return is_array($join) &&
               !empty($join['type']) &&
               $join['type'] === self::INNER_JOIN;
    }

    
    public function isLeftJoin($join)
    {
        return is_array($join) &&
               !empty($join['type']) &&
               $join['type'] === self::LEFT_JOIN;
    }

    
    public function isRightJoin($join)
    {
        return is_array($join) &&
               !empty($join['type']) &&
               $join['type'] === self::RIGHT_JOIN;
    }


    public function resolveJoinTable($join)
    {
        if (!is_array($join) || empty($join['table'])) {
            return null;
        }

        return $join['table'];
    }


    public function resolveJoinAlias($join)
    {
        if (!is_array($join) || empty($join['alias'])) {
            return null;
        }

        return $join['alias'];
    }


    public function resolveJoinOn($join)
    {
        if (!is_array($join) || empty($join['on'])) {
            return null;
        }

        return $join['on'];
    }


    public function resolveJoinFromAlias($join)
    {
        if (!is_array($join) || empty($join['from_alias'])) {
            return null;
        }

        return $join['from_alias'];
    }
}
