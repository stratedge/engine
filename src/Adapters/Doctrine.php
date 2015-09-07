<?php
namespace Stratedge\Engine\Adapters;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Stratedge\Engine\Interfaces\Adapters\Database as DatabaseAdapterInterface;
use Stratedge\Engine\Options;

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


    public function select($table, $columns = '*', Options $options = null)
    {
        $qb = $this->getConn()->createQueryBuilder();

        $qb->select($columns)
           ->from($table);

        if (!is_null($options)) {
            $qb = $this->buildQueryOptions($qb, $options);
        }

        return $qb->execute()->fetchAll();
    }


    protected function buildQueryOptions(QueryBuilder $qb, Options $options)
    {
        foreach ($options->getConds() as $cond) {
            $qb->andWhere($cond);
        }

        foreach ($options->getBinds() as $key => $value) {
            $qb->setParameter($key, $value);
        }

        if ($options->hasMax()) {
            $qb->setMaxResults($options->getMax());
        }

        if ($options->hasOffset()) {
            $qb->setFirstResult($options->getOffset());
        }

        foreach ($options->getOrders() as $order) {
            $qb->orderBy($order[0], $order[1]);
        }

        return $qb;
    }
}
