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
        } else {
            $conds = array_diff(array_keys($options), [
                'conditions',
                'bind',
                'limit',
                'order'
            ]);

            foreach ($conds as $key) {
                $options['bind'][$key] = $options[$key];
                $qb->andWhere(sprintf('%s = :%s', $key, $key));
            }
        }

        if (isset($options['bind'])) {
            foreach ($options['bind'] as $id => $value) {
                $qb->setParameter($id, $value);
            }
        }

        if (isset($options['limit'])) {
            if (is_string($options['limit']) || is_int($options['limit'])) {
                $options['limit'] = [0, $options['limit']];
            }

            $qb->setFirstResult($options['limit'][0])
               ->setMaxResults($options['limit'][1]);
        }

        if (isset($options['order'])) {

            if (is_string($options['order'])) {
                $order = [];
            
                foreach (explode(',', $options['order']) as $stmt) {
                    $parts = explode(' ', $stmt);
                    if (count($parts) === 1) {
                        $parts[1] = null;
                    }
                    $order[] = $parts;
                }
            }

            foreach($order as $stmt) {
                $qb->orderBy($stmt[0], $stmt[1]);
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
