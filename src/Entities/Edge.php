<?php
namespace Stratedge\Pumper\Entities;

use Doctrine\DBAL\Connection;
use PDO;
use Stratedge\Pumper\Entities\Node;
use Stratedge\Pumper\Entity;

class Edge extends Entity
{
    public function create(Node $a, Node $b)
    {
        $query = $this->getConn()->createQueryBuilder();

        $query->insert(
            $this->getTable()
        );

        $data = [
            'created' => time(),
            'updated' => time(),
            'deleted' => 0
        ];

        $data[$a->getTable() . '_id'] = $a->getId();
        $data[$b->getTable() . '_id'] = $b->getId();

        //Set insert properties
        foreach ($data as $property => $value) {
            $query->setValue(
                $property,
                $query->createNamedParameter($value)
            );
        }

        //Execute the insert
        $query->execute();

        //Generate a new object and set its properties
        $obj = clone $this;
        $obj->addData($data);
        $obj->id = (int) $this->getConn()->lastInsertId();

        return $obj;
    }

    public function findOppositeNode(Node $a, Node $b)
    {
        $query = $this->buildFindOppositeQuery($a, $b);

        $query->setFirstResult(0)
              ->setMaxResults(1);

        $result = $query->execute();

        if ($result->rowCount() < 1) {
            return null;
        }

        $opposite = $this->opposites[$a->getIdForEdge()];

        $id = $result->fetch(PDO::FETCH_COLUMN);

        return $b->findOneBy(['id' => $id]);
    }

    public function findOppositeNodes(Node $a, Node $b)
    {
        $query = $this->buildFindOppositeQuery($a, $b);

        $result = $query->execute();

        if ($result->rowCount() < 1) {
            return [];
        }

        $opposite = $this->opposites[$a->getIdForEdge()];

        $objs = [];

        foreach ($result->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $obj = $b->findOneBy(['id' => $id]);
            if (!is_null($obj)) {
                $objs[] = $obj;
            }
        }

        return $objs;
    }

    public function buildFindOppositeQuery(Node $a, Node $b)
    {
        $query = $this->getConn()->createQueryBuilder();

        $query->select($b->getIdForEdge())
              ->from(
                    $this->getTable()
                )
              ->orderBy('id', 'ASC');

        $query->andWhere(
            $a->getTable() . '_id' . ' = ' . $query->createNamedParameter($a->getId())
        );

        return $query;
    }
}
