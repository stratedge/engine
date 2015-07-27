<?php
namespace Stratedge\Engine\Entities;

use PDO;
use Stratedge\Engine\Entity;

class Node extends Entity
{
    /**
     * Returns the value of the id property
     * 
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Creates a new node record in the database with the given data array
     * 
     * @param  array  $data Associative array of properties and their values
     * @return object       An object representing the newly created node
     */
    public function create(array $data = [])
    {
        $query = $this->initQuery();

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
        $obj->id = (int) $this->getConn()->lastInsertId();

        return $obj;
    }


    public function update(array $data = [])
    {
        $query = $this->initQuery();
        
        $query->update(
            $this->getTable()
        );

        //Remove managed properties
        unset($data['id'], $data['created'], $data['updated'], $data['deleted']);

        $data = $this->formatProperties($data);

        $data = array_merge($data, [
            'updated' => time()
        ]);

        //Set insert properties
        foreach ($data as $property => $value) {
            $query->set(
                $property,
                $query->createNamedParameter($value)
            );
        }

        //Execute the insert
        $query->execute();

        $this->addData($data);

        return true;
    }


    /**
     * Returns the node object with property values matching the given property values and the
     * lowest id value
     * 
     * @param  array       $data Associative array of properties and values
     * @return object|null       Node object on success, otherwise null
     */
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


    /**
     * Returns node objects for each node with property values matching the given property values
     * 
     * @param  array  $data Associative array or properties and values
     * @return array        Array of node objects on success, otherwise an empty array
     */
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


    /**
     * Instantiates a new Doctrine Query Builder object and setups up the search and ordering
     * parameters
     * 
     * @param  array                            $data Associative array of properties and values
     * @return Doctrine\DBAL\Query\QueryBuilder       Doctrine Query Builder object
     */
    protected function buildFindQuery(array $data = [])
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


    /**
     * Returns a string representing the column name for the node's ID in edge tables
     * 
     * @return string
     */
    public function getIdForEdge()
    {
        return $this->getTable() . '_id';
    }
}
