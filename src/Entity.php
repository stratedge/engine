<?php
namespace Stratedge\Engine;

use Stratedge\Engine\Database;
use Stratedge\Toolbox\StringUtils;

class Entity
{
    const TYPE_INT          = 'Integer'; //Deprecated
    const TYPE_STR          = 'String'; //Deprecated
    const TYPE_INTEGER      = 'Integer';
    const TYPE_FLOAT        = 'Float';
    const TYPE_STRING       = 'String';
    const TYPE_TEXT         = 'Text';
    const TYPE_DATETIME     = 'DateTime';
    const TYPE_TIMESTAMP    = 'Timestamp';
    const TYPE_DATE         = 'Date';
    const TYPE_TIME         = 'Time';
    const TYPE_BOOL         = 'Bool';


    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $name = $this->fromGetter($name);
            return $this->$name;
        } else if (strpos($name, 'set') === 0) {
            $name = $this->fromSetter($name);
            $this->$name = $arguments[0];
            return;
        }

        //@todo Throw an exception (but which kind?)
    }


    public function __get($name)
    {
        if (property_exists($this, $name)) {
            $func = $this->toGetter($name);
            return $this->$func($value);
        }

        //@todo Throw an exception (but which kind?)
    }


    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $func = $this->toSetter($name);
            return $this->$func($value);
        }
        
        //@todo Throw an exception (but which kind?)
    }


    /**
     * @param  array           $data
     * @return EntityInterface
     */
    public static function create(array $data = [])
    {
        $obj = new static;

        $obj->addData($data);

        $obj->save();

        return $obj;
    }


    public function save()
    {
        if (is_null($this->getPrimaryKeyValue())) {
            $this->saveNew();
        } else {
            $this->saveExisting();
        }
    }


    public function saveNew()
    {
        $data = $this->getColumnData();

        $adapter = Database::getAdapter();

        $id = $adapter->insert(
            $this->getTable(),
            $this->getColumnData()
        );

        $this->setPrimaryKeyValue($id);

        return $this;
    }


    public function saveExisting()
    {

    }


    /**
     * Given an associative array of data, calls the getter for each key of the
     * array to set the column values
     * 
     * @param  array $data
     * @return self
     */
    public function addData(array $data = [])
    {
        foreach ($data as $name => $value) {
            $name = $this->toSetter($name);
            $this->$name($value);
        }

        return $this;
    }


    /**
     * Returns the name of the column marked as the entity's primary key
     * 
     * @return string
     */
    public function getPrimaryKey()
    {
        return isset($this->primary_key) ? $this->primary_key : 'id';
    }


    /**
     * Returns the value of the column defined as the entity's primary key
     * 
     * @return mixed
     */
    public function getPrimaryKeyValue()
    {
        $primary_key = $this->getPrimaryKey();
        $func = $this->toGetter($primary_key);
        return $this->$func();
    }


    /**
     * Sets the value of the property representing the table's primary key
     * 
     * @param  string|int $id
     * @return mixed
     */
    public function setPrimaryKeyValue($id)
    {
        $primary_key = $this->getPrimaryKey();
        $func = $this->toSetter($primary_key);
        return $this->$func($id);
    }


    /**
     * Returns the name of the table the entity uses
     * 
     * @return string
     */
    public function getTable()
    {
        return isset($this->table) ? $this->table : array_slice(explode('\\', get_class()), -1);
    }


    /**
     * Converts a snake-case column name to the getter function name for that
     * column
     * 
     * @param  string $name
     * @return string
     */
    public function toGetter($name)
    {
        return 'get' . StringUtils::toCamelCase($name, true);
    }


    /**
     * Converts a snake-case column name to the setter function name for that
     * column
     * 
     * @param  string $name
     * @return string
     */
    public function toSetter($name)
    {
        return 'set' . StringUtils::toCamelCase($name, true);
    }


    /**
     * Converts a camel-case getter function name for a column back to the
     * snake-case name of the column
     * 
     * @param  string $name
     * @return string
     */
    public function fromGetter($name)
    {
        return StringUtils::toSnakeCase(substr($name, 3));
    }


    /**
     * Converts a camel-case setter function name for a column back to the
     * snake-case name of the column
     * 
     * @param  string $name
     * @return string
     */
    public function fromSetter($name)
    {
        return StringUtils::toSnakeCase(substr($name, 3));
    }


    /**
     * Returns a list of columns defined for the entity
     * 
     * @return array
     */
    public function getColumns()
    {
        return array_keys($this->columns);
    }


    /**
     * Returns a list of the columns defined for the entity and the values set
     * for those columns if a value is set at all
     * 
     * @return array
     */
    public function getColumnData()
    {
        $data = [];

        foreach ($this->getColumns() as $column) {
            //Get the value
            $func = $this->toGetter($column);
            $value = $this->$func();

            //Use the value if it is not null
            if (!is_null($value)) {
                $data[$column] = $value;
            }
        }

        return $data;
    }


    /**
     * Returns the data-type of the provided snake-case column name
     * 
     * @param  string      $name
     * @return string|null       If the column does not exist, null is returned
     */
    public function getColumnType($name)
    {
        return $this->columns[$name][0];
    }
}
