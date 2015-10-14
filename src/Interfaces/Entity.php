<?php
namespace Stratedge\Engine\Interfaces;

use Doctrine\DBAL\Connection;
use Doctring\DBAL\Query\QueryBuilder;
use Stratedge\Engine\Options;

interface Entity
{
    /**
     * Magic method used to support getters and setters
     * 
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments);


    /**
     * Persists data in the database, handling both creation and updating
     * 
     * @return self
     */
    public function save();


    /**
     * Given an associative array of data, calls the getter for each key of the
     * array to set the column values
     * 
     * @param  array $data
     * @return self
     */
    public function hydrate(array $data = []);


    /**
     * Returns the name of the column marked as the entity's primary key
     * 
     * @return string
     */
    public function getPrimaryKey();


    /**
     * Returns the value of the column defined as the entity's primary key
     * 
     * @return mixed
     */
    public function getPrimaryKeyValue();


    /**
     * Sets the value of the property representing the table's primary key
     * 
     * @param  string|int $id
     * @return mixed
     */
    public function setPrimaryKeyValue($id);


    /**
     * Returns the name of the table the entity uses
     * 
     * @return string
     */
    public function getTable();


    /**
     * Returns a list of columns defined for the entity
     * 
     * @return array
     */
    public function getColumns();


    /**
     * Returns a list of the columns defined for the entity and the values set
     * for those columns if a value is set at all
     * 
     * @return array
     */
    public function getColumnData();


    /**
     * Returns the data-type of the provided snake-case column name
     * 
     * @param  string      $name
     * @return string|null       If the column does not exist, null is returned
     */
    public function getColumnType($name);


    /**
     * Returns true if the column is defined, otherwise false
     * 
     * @param  string $name
     * @return bool
     */
    public function hasColumn($name);


    /**
     * Attempts to find the row with the given id and returns an entity with
     * the row's properties.
     * If a row cannot be found, null is returned.
     * 
     * @param  int                  $id
     * @return EntityInterface|null
     */
    public static function findOne($id);


    /**
     * Attempts to find all the rows for the given ids and return an array of
     * entities with the rows' properties.
     * If no rows can be found, an empty array is returned.
     * 
     * @param  int[]             $ids An array of ids
     * @return EntityInterface[]
     */
    public static function find(array $ids);


    /**
     * Given a query options array, attemtps to find matching rows and returns
     * an array of entities representing matching rows.
     * 
     * @param  array|string      $options
     * @return EntityInterface[]
     */
    public static function findBy(Options $options);


    /**
     * Given a query options array, attempts to find the first matching row and
     * returns an entity representing the matching row.
     * If a row cannot be found, null will be returned.
     * 
     * @param  array|string      $options
     * @return EntityInterface[]
     */
    public static function findOneBy(Options $options);
}
