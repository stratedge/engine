<?php
namespace Stratedge\Engine;

use DateTime;
use Stratedge\Engine\Database;
use Stratedge\Engine\Interfaces\Entity as EntityInterface;
use Stratedge\Toolbox\NumberUtils;
use Stratedge\Toolbox\StringUtils;

abstract class Entity implements EntityInterface
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


    /**
     * Magic method used to support getters and setters
     * 
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $name = $this->fromGetter($name);
            return $this->$name;
        } else if (strpos($name, 'set') === 0) {
            $name = $this->fromSetter($name);
            $this->$name = $this->formatData($name, $arguments[0]);
            return;
        }

        //@todo Throw an exception (but which kind?)
    }


    /**
     * Magic method used to support getters
     * 
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            $func = $this->toGetter($name);
            return $this->$func($value);
        }

        //@todo Throw an exception (but which kind?)
    }


    /**
     * Magic method used to support setters
     * 
     * @param  string $name
     * @param  mixed  $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $func = $this->toSetter($name);
            return $this->$func($value);
        }
        
        //@todo Throw an exception (but which kind?)
    }


    /**
     * Persists data in the database, handling both creation and updating
     * 
     * @return self
     */
    public function save()
    {
        if (is_null($this->getPrimaryKeyValue())) {
            return $this->saveNew();
        } else {
            return $this->saveExisting();
        }
    }


    /**
     * Creates a new record in the database
     * 
     * @return self
     */
    protected function saveNew()
    {
        if (
            $this->hasColumn('created') &&
            $this->isColumnDateType('created')
        ) {
            $this->setCreated(date('Y-m-d H:i:s'));
        }

        if (
            $this->hasColumn('updated') &&
            $this->isColumnDateType('updated')
        ) {
            $this->setUpdated(date('Y-m-d H:i:s'));
        }

        if (
            $this->hasColumn('deleted') &&
            $this->isColumnDateType('deleted')
        ) {
            $this->setDeleted('0000-00-00 00:00:00');
        }

        $adapter = Database::getAdapter();

        $id = $adapter->insert(
            $this->getTable(),
            $this->getColumnData()
        );

        $this->setPrimaryKeyValue($id);

        return $this;
    }


    /**
     * Updates an existing record in the database
     * 
     * @return self
     */
    protected function saveExisting()
    {
        if (
            $this->hasColumn('updated') &&
            $this->isColumnDateType('updated')
        ) {
            $this->setUpdated(date('Y-m-d H:i:s'));
        }

        $adapter = Database::getAdapter();

        $id = $adapter->update(
            $this->getTable(),
            $this->getColumnData(),
            [
                $this->getPrimaryKey() . ' = :id',
                'bind' => [
                    $this->getPrimaryKey() => $this->getPrimaryKeyValue()
                ]
            ]
        );

        return $this;
    }


    /**
     * Given an associative array of data, calls the getter for each key of the
     * array to set the column values
     * 
     * @param  array $data
     * @return self
     */
    public function hydrate(array $data = [])
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
        if (isset($this->table)) {
            return $this->table;
        } else {
            $table = end(explode('\\', get_class($this)));
            return StringUtils::toSnakeCase($table);
        }            
    }


    /**
     * Converts a snake-case column name to the getter function name for that
     * column
     * 
     * @param  string $name
     * @return string
     */
    protected function toGetter($name)
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
    protected function toSetter($name)
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
    protected function fromGetter($name)
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
    protected function fromSetter($name)
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


    /**
     * Returns true if the column is defined, otherwise false
     * 
     * @param  string $name
     * @return bool
     */
    public function hasColumn($name)
    {
        return in_array($name, $this->getColumns());
    }


    /**
     * Returns true if the given column is expected to contain a date, otherwise
     * false
     * 
     * @param  string $name The name of the column to evaluate
     * @return bool
     */
    protected function isColumnDateType($name)
    {
        return in_array(
            $this->getColumnType($name),
            [
                self::TYPE_DATETIME,
                self::TYPE_TIMESTAMP,
                self::TYPE_DATE
            ]
        );
    }


    /**
     * Returns the correct format for the value provided depending on the type
     * of the given column
     * 
     * @param  string $name  The name of the column to format data for
     * @param  mixed  $value The value to format
     * @return mixed
     */
    protected function formatData($name, $value)
    {
        if (!$this->hasColumn($name)) return $value;

        switch ($this->getColumnType($name)) {
            case self::TYPE_INT:
            case self::TYPE_INTEGER:
                return (int) $value;
            case self::TYPE_STR:
            case self::TYPE_STRING:
            case self::TYPE_TEXT:
                return (string) $value;
            case self::TYPE_FLOAT:
                return (float) $value;
            case self::TYPE_DATETIME:
            case self::TYPE_TIMESTAMP:
            case self::TYPE_DATE:
            case self::TYPE_TIME:
                return $this->formatDate($value, $this->getColumnType($name));
            case self::TYPE_BOOL:
                return (bool) $value;
        }

        return $value;
    }


    /**
     * Given a timestamp or date string, and a column type, returns the correct
     * representation of the date/time for the column type
     * 
     * @param  string     $value
     * @param  string|int $type
     * @return string|int
     */
    protected function formatDate($value, $type)
    {
        //An empty SQL date/time needs to be handled as a special case
        if (
            preg_match(
                '/^(0000-00-00|0000-00-00 00:00:00|00:00:00)$/',
                $value
            ) === 1
        ) {
            switch ($type) {
                case self::TYPE_DATETIME:
                case self::TYPE_TIMESTAMP:
                    return '0000-00-00 00:00:00';
                case self::TYPE_DATE:
                    return '0000-00-00';
                case self::TYPE_TIME:
                    return '00:00:00';
            }

            return $value;
        }

        if (NumberUtils::isOnlyNumbers($value)) {
            $dt = new DateTime();
            $dt->setTimestamp($value);
        } else {
            $dt = new DateTime($value);
        }

        switch ($type) {
            case self::TYPE_DATETIME:
            case self::TYPE_TIMESTAMP:
                return $dt->format('Y-m-d H:i:s');
            case self::TYPE_DATE:
                return $dt->format('Y-m-d');
            case self::TYPE_TIME:
                return $dt->format('H:i:s');
        }

        return $value;
    }
}
