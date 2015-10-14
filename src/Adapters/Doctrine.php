<?php
namespace Stratedge\Engine\Adapters;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Stratedge\Engine\Factory;
use Stratedge\Engine\Interfaces\Adapters\Database as DatabaseAdapterInterface;
use Stratedge\Engine\Interfaces\Options as OptionsInterface;

class Doctrine implements DatabaseAdapterInterface
{
    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->setConn($conn);
    }


    public function getConn()
    {
        return $this->conn;
    }


    public function setConn($conn)
    {
        $this->conn = $conn;
        return $this;
    }


    /**
     * @param  string $table
     * @param  array  $data
     * @return int
     */
    public function insert($table, array $data)
    {
        $qb = $this->getConn()->createQueryBuilder();

        $qb->insert($table);

        foreach ($data as $column => $value) {
            $qb->setValue($column, $qb->createNamedParameter($value));
        }

        $qb->execute();

        return $this->getConn()->lastInsertId();
    }


    public function update($table, array $data, array $options = [])
    {
        $qb = $this->getConn()->createQueryBuilder();

        $qb->update($table);

        foreach ($data as $column => $value) {
            $qb->set($column, $qb->createNamedParameter($value));
        }

        $qb = $this->buildQueryOptions($qb, $options);

        return $qb->execute();
    }


    public function select($table, $columns = '*', OptionsInterface $options = null)
    {
        $qb = $this->getConn()->createQueryBuilder();

        $qb->select($columns)
           ->from($table, $options ? $options->getAlias() : null);

        if (!is_null($options)) {
            $qb = $this->buildQueryOptions($qb, $options);
        }

        $result = Factory::assemble('\\Stratedge\\Engine\\Result', [$qb->execute()]);

        return $result;
    }


    protected function buildQueryOptions(QueryBuilder $qb, OptionsInterface $options)
    {
        //Construct the JOIN clause(s)
        foreach ($options->getJoin() as $join) {
            $table = $options->resolveJoinTable($join);
            $alias = $options->resolveJoinAlias($join);
            $on = $options->resolveJoinOn($join);
            $from_alias = $options->resolveJoinFromAlias($join);

            switch (true) {
                case $options->isJoin($join):
                    $qb->join($from_alias, $table, $alias, $on);
                    break;
                case $options->isInnerJoin($join):
                    $qb->innerJoin($from_alias, $table, $alias, $on);
                    break;
                case $options->isLeftJoin($join):
                    $qb->leftJoin($from_alias, $table, $alias, $on);
                    break;
                case $options->isRightJoin($join):
                    $qb->rightJoin($from_alias, $table, $alias, $on);
                    break;
            }
        }

        //Construct the WHERE clause
        foreach ($options->getWhere() as $where) {
            if ($options->isAnd($where)) {
                $qb->andWhere(
                    $options->resolveCondition($where)
                );
            } elseif ($options->isOr($where)) {
                $qb->orWhere(
                    $options->resolveCondition($where)
                );
            }
        }

        //Bind data
        foreach ($options->getData() as $key => $value) {
            $qb->setParameter($key, $value);
        }

        //Construct the GROUP BY clause
        foreach ($options->getGroupBy() as $group_by) {
            $qb->addGroupBy($group_by);
        }

        //Construct the HAVING clause
        foreach ($options->getHaving() as $having) {
            if ($options->isAnd($having)) {
                $qb->andHaving(
                    $options->resolveCondition($having)
                );
            } elseif ($options->isOr($having)) {
                $qb->orHaving(
                    $options->resolveCondition($having)
                );
            }
        }

        //Construct the ORDER BY clause
        foreach ($options->getOrderBy() as $order_by) {
            $qb->orderBy(
                $options->resolveColumn($order_by),
                $options->resolveDirection($order_by)
            );
        }

        //Set the max rows to return
        if ($options->getMax()) {
            $qb->setMaxResults($options->getMax());
        }

        //Set the row offset
        if ($options->getOffset()) {
            $qb->setFirstResult($options->getOffset());
        }

        return $qb;
    }
}
