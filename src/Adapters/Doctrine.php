<?php
namespace Stratedge\Engine\Adapters;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Stratedge\Engine\Interfaces\Adapters\Database as DatabaseAdapterInterface;

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


    public function buildQueryOptions(QueryBuilder $qb, array $options = [])
    {
        if (isset($options[0]) && !isset($options['conditions'])) {
            $options['conditions'] = $options[0];
        }

        if (isset($options['conditions'])) {
            $qb->where($options['conditions']);
        }

        if (isset($options['bind'])) {
            foreach ($options['bind'] as $id => $value) {
                $qb->setParameter($id, $value);
            }
        }

        return $qb;
    }


    public function select($table, $columns = '*', array $options = [])
    {
        $qb = $this->getConn()->createQueryBuilder();

        $qb->select($columns)
           ->from($table);

        $qb = $this->buildQueryOptions($qb, $options);

        return $qb->execute()->fetchAll();
    }
}
