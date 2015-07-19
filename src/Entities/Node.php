<?php
namespace Stratedge\Pumper\Entities;

use Doctrine\DBAL\Connection;
use PDO;

class Node
{
    const TYPE_INT      = 'Int';
    const TYPE_STR      = 'Str';
    const TYPE_PASSWORD = 'Password';

    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->setConn($conn);
    }

    public function getConn()
    {
        return $this->conn;
    }

    public function setConn(Connection $conn)
    {
        $this->conn = $conn;
        return $this;
    }

    public function create(array $data = [])
    {
        $query = $this->getConn()->createQueryBuilder();

        $query->insert(
            $this->getTable()
        );

        //Remove managed properties
        unset($data['id'], $data['created'], $data['updated'], $data['deleted']);

        $data = $this->formatProperties($data);

        $data = array_merge($data, [
            'created' => time(),
            'updated' => time(),
            'deleted' => 0
        ]);

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
        $obj->id = $this->getConn()->lastInsertId();

        return $obj;
    }

    public function findOneBy(array $data = [])
    {
        $query = $this->buildFindQuery($data);

        $query->setFirstResult(0)
              ->setMaxResults(1);

        $result = $query->execute();

        if ($result->rowCount() < 1) {
            return null;
        }

        $obj = clone $this;
        $obj->addData(
            $this->formatProperties(
                $result->fetch(PDO::FETCH_NAMED)
            )
        );

        return $obj;
    }

    public function findBy(array $data = [])
    {
        $query = $this->buildFindQuery($data);

        $result = $query->execute();

        if ($result->rowCount() < 1) {
            return [];
        }

        $objs = [];

        foreach ($result->fetchAll(PDO::FETCH_NAMED) as $row) {
            $obj = clone $this;
            $obj->addData(
                $this->formatProperties(
                    $row
                )
            );

            $objs[] = $obj;
        }

        return $objs;
    }

    public function buildFindQuery(array $data = [])
    {
        $query = $this->getConn()->createQueryBuilder();

        $query->select('*')
              ->from(
                    $this->getTable()
                )
              ->orderBy('id', 'ASC');

        $data = $this->formatProperties($data);

        foreach ($data as $property => $value) {
            $query->andWhere(
                $property . ' = ' . $query->createNamedParameter($value)
            );
        }

        return $query;
    }

    public function getTable()
    {
        return to_snake_case(
            end(
                explode('\\', get_class($this))
            )
        );
    }

    public function formatProperties(array $data = [])
    {
        $final = [];

        foreach ($this->properties as $property => $params) {
            if (isset($data[$property])) {
                switch($this->getPropertyType($property)) {
                    case self::TYPE_INT:
                        $final[$property] = (int) $data[$property];
                        break;
                    case self::TYPE_STR:
                        $final[$property] = (string) $data[$property];
                        break;
                }
            }
        }

        return $final;
    }

    public function getPropertyType($property)
    {
        if (is_array($this->properties[$property])) {
            //Handle params
        } else {
            return $this->properties[$property];
        }
    }

    public function addData(array $data = [])
    {
        foreach (array_keys($this->properties) as $property) {
            if (isset($data[$property])) {
                $this->{$property} = $data[$property];
            }
        }
    }
}
