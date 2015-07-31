<?php
namespace Stratedge\Engine;

use Doctrine\DBAL\Connection;

class Entity
{
    const TYPE_INT      = 'Int';
    const TYPE_STR      = 'Str';

    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->setConn($conn);
    }


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
     * Retrieve the database connection object from the conn property
     * 
     * @return Doctrine\DBAL\Connection Doctrine Connection object
     */
    public function getConn()
    {
        return $this->conn;
    }


    /**
     * Sets the given database connection object to the conn property
     * 
     * @param Doctrine\DBAL\Connection $conn Doctrine Connection object
     */
    public function setConn(Connection $conn)
    {
        $this->conn = $conn;
        return $this;
    }


    /**
     * Returns a new Doctrine Query Builder object
     * 
     * @return Doctrine\DBAL\Query\QueryBuilder
     */
    public function initQuery()
    {
        return $this->getConn()->createQueryBuilder();
    }


    /**
     * Returns an associative array of property values converted to the correct data type, excluding
     * properties not defined for the entity's schema
     * 
     * @param  array  $data Associative array of properties and values
     * @return array        Associative array of properties and values
     */
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


    /**
     * Retrieves the data type for the given property from the defined schema
     * 
     * @param  string $property The property to retrieve the data type for
     * @return string           The data type as expressed as a value of the self::TYPE_* constants
     */
    public function getPropertyType($property)
    {
        if (is_array($this->properties[$property])) {
            //Handle params
        } else {
            return $this->properties[$property];
        }
    }


    /**
     * Returns the name of the table for the given entity
     * 
     * @return string The name of the table for the given entity
     */
    public function getTable()
    {
        $ns_class = get_class($this);
        $parts = explode('\\', $ns_class);
        $class = end($parts);

        return to_snake_case($class);
    }


    /**
     * Sets the properties defined in the given associative array to values provided
     * 
     * @param array $data Associative array of properties and their values
     */
    public function addData(array $data = [])
    {
        foreach (array_keys($this->properties) as $property) {
            if (isset($data[$property])) {
                $this->{$property} = $data[$property];
            }
        }
    }
}
